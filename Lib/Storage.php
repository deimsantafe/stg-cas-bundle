<?php

namespace STG\DEIM\Security\Bundle\CasBundle\Lib;

/**
 * Almacenamiento simple de PGT (Proxy Granting Ticket) en archivo JSON.
 * Usado únicamente en modo proxy.
 */
class Storage
{
    public function __construct(private readonly string $filename) {}

    public function addPgt(string $pgt, string $pgtiou): void
    {
        $content   = $this->getFileContent();
        $content[] = ['pgt' => $pgt, 'pgtiou' => $pgtiou];
        $this->saveToFile($content);
    }

    /**
     * @throws \Exception si no se encuentra el PGTIOU
     */
    public function getPgt(string $pgtiou): string
    {
        $content = $this->getFileContent();

        foreach ($content as $key => $row) {
            if ($row['pgtiou'] === $pgtiou) {
                unset($content[$key]);
                $this->saveToFile($content);

                return $row['pgt'];
            }
        }

        throw new \Exception(sprintf('PGT no encontrado para PGTIOU "%s"', $pgtiou));
    }

    /** @return array<int, array{pgt: string, pgtiou: string}> */
    protected function getFileContent(): array
    {
        if (!file_exists($this->filename)) {
            return [];
        }

        return json_decode(file_get_contents($this->filename), true) ?? [];
    }

    /** @param array<int, array{pgt: string, pgtiou: string}> $content */
    protected function saveToFile(array $content): void
    {
        file_put_contents($this->filename, json_encode(array_values($content)));
    }
}
