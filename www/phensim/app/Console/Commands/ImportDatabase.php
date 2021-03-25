<?php

namespace App\Console\Commands;

use App\Models\Organism;
use App\PHENSIM\Utils;
use DB;
use Illuminate\Console\Command;

class ImportDatabase extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'import:database';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Import MITHrIL 2 organisms/nodes database.';

    /**
     * Read nodes exported from MITHrIL2 database
     *
     * @param  string  $file
     * @param  \App\Models\Organism  $organism
     *
     * @return bool
     */
    private function readNodes(string $file, Organism $organism): bool
    {
        if (!file_exists($file)) {
            return false;
        }
        $fp = fopen($file, 'rb');
        if (!$fp) {
            return false;
        }
        $lines = Utils::countLines($file) - 1;
        $this->output->progressStart($lines);
        $data = [];
        while (($line = fgetcsv($fp, 0, "\t")) !== false) {
            $this->output->progressAdvance();
            if (count($line) < 4 || str_starts_with($line[0], '#')) {
                continue;
            }
            $data[] = ['accession' => $line[0], 'name' => $line[1], 'aliases' => $line[3], 'organism_id' => $organism->id];
            if (count($data) === 100) {
                DB::table('nodes')->insertOrIgnore($data);
                $data = [];
            }
        }
        $this->output->progressFinish();

        return true;
    }

    /**
     * Read index of reactome pathways
     *
     * @return array
     */
    private function readReactomeIndex(): array
    {
        $organisms = [];
        $fp = gzopen('https://alpha.dmi.unict.it/mithril/data/reactome/index.txt.gz', 'rb');
        if (!$fp) {
            return $organisms;
        }
        while (($line = fgetcsv($fp, 0, "\t")) !== false) {
            if (count($line) >= 2) {
                $organisms[] = $line[0];
            }
        }

        return array_unique($organisms);
    }

    /**
     * Read organisms index
     *
     * @return array
     */
    private function readOrganismsIndex(): array
    {
        $index = [];
        $fp = gzopen('https://alpha.dmi.unict.it/mithril/data/index.txt.gz', 'rb');
        if (!$fp) {
            return $index;
        }
        while (($line = fgetcsv($fp, 0, "\t")) !== false) {
            if (count($line) >= 2) {
                $index[$line[0]] = $line[1];
            }
        }

        return $index;
    }

    /**
     * Export nodes from MITHrIL2
     *
     * @param  \App\Models\Organism  $organism
     *
     * @return string|null
     */
    private function exportNodes(Organism $organism): ?string
    {
        $file = tempnam(storage_path('app/'), 'nodes_');
        $cmd = [
            config('phensim.java'),
            '-jar',
            config('phensim.mithril'),
            'exportgraph',
            '-enrichment-evidence-type',
            'WEAK',
            '-verbose',
            '-organism',
            $organism->accession,
            '-no',
            $file,
        ];
        if ($organism->has_reactome) {
            $cmd[] = '-reactome';
        }
        Utils::runCommand(
            $cmd,
            callback: function ($type, $buffer) {
            $this->output->write($buffer);
        }
        );
        if (!file_exists($file)) {
            $this->error('Unable to find output file ' . $file);

            return null;
        }

        return $file;
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle(): int
    {
        $reactomeIndex = $this->readReactomeIndex();
        $organismsIndex = $this->readOrganismsIndex();
        foreach ($organismsIndex as $accession => $name) {
            $this->info('Importing ' . $name);
            $organism = Organism::create(
                [
                    'name'         => $name,
                    'accession'    => $accession,
                    'has_reactome' => in_array($accession, $reactomeIndex, true),
                ]
            );
            $this->info('Exporting nodes...');
            if (($file = $this->exportNodes($organism)) !== null) {
                $this->info('Processing nodes...');
                $this->readNodes($file, $organism);
                @unlink($file);
            }
        }

        return 0;
    }
}
