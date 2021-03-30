<?php

namespace Database\Seeders;

use App\Models\Organism;
use Illuminate\Database\Seeder;

class OrganismsTableSeeder extends Seeder
{

    private const TEST_ORGANISMS = [
        'hsa' => ['Homo Sapiens', true],
        'mmu' => ['Mus Musculus', true],
        'rno' => ['Rattus Norvegicus', false],
    ];

    private const MAX_NODES = 100;

    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run(): void
    {
        foreach (self::TEST_ORGANISMS as $accession => $data) {
            $organism = Organism::create(
                [
                    'accession'    => $accession,
                    'name'         => $data[0],
                    'has_reactome' => $data[1],
                ]
            );
            for ($i = 1; $i <= self::MAX_NODES; $i++) {
                $organism->nodes()->create(
                    [
                        'accession' => 'G' . $i,
                        'name'      => 'GENE' . $i,
                        'aliases'   => [],
                    ]
                );
            }
        }
    }
}
