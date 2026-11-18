<?php

declare(strict_types=1);

namespace GeekCo\FilamentMaxUsers\Tests\Unit;

use GeekCo\FilamentMaxUsers\Tests\TestCase;

final class LocalizationTest extends TestCase
{
    public function testRuAndEnLangFilesHaveSameKeys(): void
    {
        foreach (['users', 'chats'] as $file) {
            /** @var array<string, mixed> $ru */
            $ru = require __DIR__.'/../../lang/ru/'.$file.'.php';

            /** @var array<string, mixed> $en */
            $en = require __DIR__.'/../../lang/en/'.$file.'.php';

            $ru = $this->flatten($ru);
            $en = $this->flatten($en);

            self::assertSame(
                array_keys($ru),
                array_keys($en),
                'Language keys differ for '.$file.'.php',
            );
        }
    }

    /**
     * @param array<array-key, mixed> $messages
     *
     * @return array<string, string>
     */
    private function flatten(array $messages, string $prefix = ''): array
    {
        $flattened = [];

        foreach ($messages as $key => $value) {
            if (\is_array($value)) {
                $flattened += $this->flatten($value, $prefix.$key.'.');

                continue;
            }

            $flattened[$prefix.$key] = \is_string($value) ? $value : '';
        }

        \ksort($flattened);

        return $flattened;
    }
}
