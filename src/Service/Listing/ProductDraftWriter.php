<?php declare(strict_types=1);

namespace JoostGroen\Mentat\Service\Listing;

use JoostGroen\Mentat\Service\Listing\DescriptionRenderer;
use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\DataAbstractionLayer\EntityRepository;
use Shopware\Core\Framework\Uuid\Uuid;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Filter\EqualsFilter;
use Shopware\Core\System\SystemConfig\SystemConfigService;

class ProductDraftWriter
{
    public function __construct(
        private EntityRepository $productRepository,
        private EntityRepository $taxRepository,
        private DescriptionRenderer $descriptionRenderer,
        private SystemConfigService $systemConfigService,
    ) {
    }

    public function write(array $template, array $values, string $name, string $productNumber, float $price, int $stock, Context $context): string
    {
        if (!$this->isProductNumberUnique($productNumber, $context)) {
            throw new \InvalidArgumentException('Product number is not unique');
        }

        $id = Uuid::randomHex();
        $taxId = $this->getDefaultTaxId();
        $taxRate = $this->getTaxRate($taxId, $context);
        $description = $this->descriptionRenderer->render($template, $values, $name);


        $this->productRepository->create([
            [
                'id'            => $id,
                'name'          => $name,
                'productNumber' => $productNumber,
                'stock'         => $stock,
                'price'         => [
                    [
                        'currencyId' => \Shopware\Core\Defaults::CURRENCY,
                        'gross'      => $price,
                        'net'        => $price / (1 + ($taxRate / 100)),
                        'linked'     => true,
                    ]
                ],
                'taxId'         => $taxId,
                'description'   => $description,
                'active'        => false,
                'metaTitle'      => $values['seoTitle'] ?? '',
                'metaDescription' => $values['seoDescription'] ?? '',
            ]
        ], $context);

        return $id;
    }

    private function getTaxRate(string $taxId, Context $context): float
    {
        $tax = $this->taxRepository->search(new Criteria([$taxId]), $context)->getEntities()->first();

        if ($tax === null) {
            throw new \InvalidArgumentException(sprintf('Tax with ID "%s" not found', $taxId));
        }

        return $tax->getTaxRate();
    }

    private function getDefaultTaxId(): string
    {
        $taxId = $this->systemConfigService->get('core.tax.defaultTaxRate');
        if (!is_string($taxId) || $taxId === '') {
            throw new \RuntimeException('No default tax configured in the shop');
        }

        return $taxId;
    }

    private function isProductNumberUnique(string $productNumber, Context $context): bool
    {
        $criteria = new Criteria();
        $criteria->addFilter(new EqualsFilter('productNumber', $productNumber));
        $product = $this->productRepository->search($criteria, $context)->getEntities()->first();
        return $product === null;
    }

    

    
}