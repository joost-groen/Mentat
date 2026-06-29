<?php declare(strict_types=1);

namespace JoostGroen\Mentat\Controller\Admin;

use JoostGroen\Mentat\Core\Content\MentatCategory\MentatCategoryEntity;
use JoostGroen\Mentat\Service\Listing\ProductDraftWriter;
use JoostGroen\Mentat\Service\Llm\LlmClientInterface;
use JoostGroen\Mentat\Service\Llm\PromptBuilder;
use JoostGroen\Mentat\Service\Llm\ResultValidator;
use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\DataAbstractionLayer\EntityRepository;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Shopware\Core\Framework\Uuid\Uuid;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;

#[Route(defaults: ['_routeScope' => ['api']])]
class ListingDraftController
{
    public function __construct(
        private readonly EntityRepository $categoryRepository,
        private readonly PromptBuilder $promptBuilder,
        private readonly LlmClientInterface $llm,
        private readonly ResultValidator $resultValidator,
        private readonly ProductDraftWriter $productDraftWriter,
    ) {
    }

    #[Route(
        path: '/api/_action/mentat/listing/draft',
        name: 'api.action.mentat.listing.draft',
        methods: ['POST']
    )]
    public function create(Request $request, Context $context): JsonResponse
    {
        $validation = $this->validateRequest($request);

        if ($validation !== []) {
            return new JsonResponse(['errors' => $validation], JsonResponse::HTTP_BAD_REQUEST);
        }

        $categoryId = (string) $request->request->get('categoryId');
        $category = $this->loadCategory($categoryId, $context);

        if ($category === null) {
            return new JsonResponse(['errors' => ['Category could not be found.']], JsonResponse::HTTP_NOT_FOUND);
        }

        $template = $category->getTemplate() ?? ['sections' => []];
        $builtPrompt = $this->promptBuilder->build($template);
        $pdf = $request->files->get('pdf');
        $temporaryPath = null;

        try {
            if (!$pdf instanceof UploadedFile) {
                throw new \InvalidArgumentException('Upload a spec-sheet PDF.');
            }

            $temporaryPath = $this->copyUploadToTemporaryFile($pdf);
            $result = $this->llm->extract($temporaryPath, $builtPrompt->prompt, $builtPrompt->schema);
            $validationResult = $this->resultValidator->validate($builtPrompt->schema, $result);

            $productId = $this->productDraftWriter->write(
                $template,
                $result,
                trim((string) $request->request->get('name')),
                trim((string) $request->request->get('productNumber')),
                (float) $request->request->get('price'),
                (int) $request->request->get('stock'),
                $context
            );
        } catch (\InvalidArgumentException $exception) {
            return new JsonResponse(
                ['errors' => [$exception->getMessage()]],
                JsonResponse::HTTP_BAD_REQUEST
            );
        } catch (\Throwable $exception) {
            return new JsonResponse(
                ['errors' => [$exception->getMessage()]],
                JsonResponse::HTTP_INTERNAL_SERVER_ERROR
            );
        } finally {
            if ($temporaryPath !== null && is_file($temporaryPath)) {
                unlink($temporaryPath);
            }
        }

        return new JsonResponse([
            'productId' => $productId,
            'extractedValues' => $validationResult->values,
            'missingFields' => $validationResult->missing,
            'emptyFields' => $validationResult->emptyFields,
        ]);
    }

    /**
     * @return list<string>
     */
    private function validateRequest(Request $request): array
    {
        $errors = [];
        $categoryId = trim((string) $request->request->get('categoryId'));
        $name = trim((string) $request->request->get('name'));
        $productNumber = trim((string) $request->request->get('productNumber'));
        $price = $request->request->get('price');
        $stock = $request->request->get('stock');
        $pdf = $request->files->get('pdf');

        if ($categoryId === '' || !Uuid::isValid($categoryId)) {
            $errors[] = 'Choose a valid Mentat category.';
        }

        if ($name === '') {
            $errors[] = 'Product name is required.';
        }

        if ($productNumber === '') {
            $errors[] = 'Product number is required.';
        }

        if (!is_numeric($price) || (float) $price < 0) {
            $errors[] = 'Price must be zero or a positive number.';
        }

        if (!is_numeric($stock) || (int) $stock < 0) {
            $errors[] = 'Stock must be zero or a positive whole number.';
        }

        if (!$pdf instanceof UploadedFile) {
            $errors[] = 'Upload a spec-sheet PDF.';
        } elseif (!$this->isPdfUpload($pdf)) {
            $errors[] = 'The uploaded file must be a PDF.';
        }

        return $errors;
    }

    private function loadCategory(string $categoryId, Context $context): ?MentatCategoryEntity
    {
        $category = $this->categoryRepository->search(new Criteria([$categoryId]), $context)->getEntities()->first();

        return $category instanceof MentatCategoryEntity ? $category : null;
    }

    private function isPdfUpload(UploadedFile $pdf): bool
    {
        $mimeType = $pdf->getMimeType();
        $extension = strtolower((string) $pdf->getClientOriginalExtension());

        return $mimeType === 'application/pdf' || $extension === 'pdf';
    }

    private function copyUploadToTemporaryFile(UploadedFile $pdf): string
    {
        $temporaryPath = tempnam(sys_get_temp_dir(), 'mentat_listing_');

        if ($temporaryPath === false || !copy($pdf->getPathname(), $temporaryPath)) {
            throw new \RuntimeException('Could not store the uploaded PDF temporarily.');
        }

        return $temporaryPath;
    }
}
