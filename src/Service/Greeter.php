<?php declare(strict_types=1);

namespace JoostGroen\Mentat\Service;

class Greeter
{
    public function greet(string $name): string
    {
        return "Hello, $name!";
    }
}