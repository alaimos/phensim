<?php

namespace App\Console\Commands;

use App\Models\Edge;
use App\Models\Node;
use App\Models\Organism;
use App\Models\Pathway;
use App\SIMPATHY\Utils;
use Illuminate\Console\Command;

class ImportPathways extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'import:pathways';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Import pathways in the database';

    protected $m2;
    protected $nodesFile;
    protected $edgesFile;
    protected $mapFile;
    protected $nodesMap = [];


    /**
     * Create a new command instance.
     */
    public function __construct()
    {
        parent::__construct();
        $this->m2 = resource_path('bin/MITHrIL2.jar');
        $this->nodesFile = storage_path('app/graph/nodes.txt');
        $this->edgesFile = storage_path('app/graph/edges.txt');
        $this->mapFile = storage_path('app/graph/map.txt');
    }

    /**
     * Export organisms from mithril 2
     *
     * @return array
     */
    protected function exportOrganisms(): array
    {
        $this->info("Exporting MITHrIL 2 organisms");
        $m2 = resource_path('bin/MITHrIL2.jar');
        $command = 'java -jar ' . escapeshellarg($m2) . ' exporg';
        //$output = null;
        //$return = null;
        //exec($command, $output, $return);
        $return = 0;
        $output = [
            "mmu\tMus musculus (mouse)",
            "hsa\tHomo sapiens (human)",
            "rno\tRattus norvegicus (rat)",
        ];
        if ($return == 0) {
            $organisms = [];
            foreach ($output as $line) {
                if (!empty($line) && $line{0} != '#') {
                    $data = explode("\t", $line);
                    if (count($data) >= 2) {
                        $organisms[$data[0]] = Organism::create([
                            'accession' => $data[0],
                            'name'      => $data[1],
                        ]);
                    }
                }
            }
            $this->info("Done!");
            return $organisms;
        } else {
            $this->error("An error occurred!");
        }
        return [];
    }

    /**
     * Export pathways from mithril 2
     *
     * @param string $organism
     *
     * @return bool
     */
    protected function exportPathways(string $organism): bool
    {
        $this->info("Exporting MITHrIL 2 pathways for organism " . $organism);
        $m2 = resource_path('bin/MITHrIL2.jar');
        $command = 'java -jar ' . escapeshellarg($m2) . ' exportgraph -verbose -organism ' . escapeshellarg($organism) .
                   ' -enrichment-evidence-type STRONG -exclude-categories "Endocrine and metabolic diseases,' .
                   'Neurodegenerative diseases,Human Diseases,Immune diseases,Infectious diseases,Cardiovascular ' .
                   'diseases" -no ' . escapeshellarg($this->nodesFile) . ' -eo ' . escapeshellarg($this->edgesFile) .
                   ' -mo ' . escapeshellarg($this->mapFile);
        $return = null;
        passthru($command, $return);
        if ($return == 0) {
            $this->info("Done!");
        } else {
            $this->error("An error occurred!");
        }
        return ($return == 0);
    }

    /**
     * Import nodes and creates a map from accession number to internal Id
     *
     * @return bool
     *
     */
    protected function importNodes(): bool
    {
        $this->info("Importing nodes");
        $count = Utils::countLines($this->nodesFile);
        $fp = fopen($this->nodesFile, 'r');
        if (!$fp) {
            $this->error("Unable to read nodes file.");
            return false;
        }
        $bar = $this->output->createProgressBar($count);
        while (($line = fgets($fp)) !== false) {
            $bar->advance();
            $line = str_replace(["\r", "\n"], "", $line);
            if (strlen($line) == 0 || $line{0} == '#') {
                continue;
            }
            $fields = explode("\t", $line);
            if (count($fields) == 4) {
                $accession = $fields[0];
                if (!isset($this->nodesMap[$accession])) {
                    $node = Node::create([
                        'accession' => $fields[0],
                        'name'      => $fields[1],
                        'type'      => strtolower($fields[2]),
                        'aliases'   => (array)array_filter(array_map('trim', explode(',', $fields[3]))),
                    ]);
                    $this->nodesMap[(string)$node->accession] = $node->id;
                }
            }
        }
        $bar->finish();
        $this->info("\nDone!");
        return true;
    }

    /**
     * Import edges
     *
     * @param \App\Models\Organism $organism
     *
     * @return bool
     */
    protected function importEdges(Organism $organism): bool
    {
        /** @var Edge[] $tmpMap */
        $tmpMap = [];
        $this->info("Importing edges");
        $count = Utils::countLines($this->edgesFile);
        $fp = fopen($this->edgesFile, 'r');
        if (!$fp) {
            $this->error("Unable to read edges file.");
            return false;
        }
        $bar = $this->output->createProgressBar($count);
        while (($line = fgets($fp)) !== false) {
            $bar->advance();
            $line = str_replace(["\r", "\n"], "", $line);
            if (strlen($line) == 0 || $line{0} == '#') {
                continue;
            }
            $fields = explode("\t", $line);
            if (count($fields) == 4) {
                $edgeId = Edge::computeId((string)$fields[0], (string)$fields[1], $organism->id);
                if (!isset($tmpMap[$edgeId])) {
                    $tmpMap[$edgeId] = $edge = Edge::create([
                        'start_id'    => $this->nodesMap[(string)$fields[0]],
                        'end_id'      => $this->nodesMap[(string)$fields[1]],
                        'types'       => [],
                        'organism_id' => $organism->id,
                    ]);
                }
                $types = $tmpMap[$edgeId]->types;
                $types[] = [$fields[2], $fields[3]];
                $tmpMap[$edgeId]->types = $types;
                $tmpMap[$edgeId]->save();
            }
        }
        $bar->finish();
        $this->info("\nDone!");
        return true;
    }

    /**
     * Import pathways
     *
     * @param \App\Models\Organism $organism
     *
     * @return bool
     */
    protected function importPathways(Organism $organism): bool
    {
        /** @var Pathway[] $tmpMap */
        $tmpMap = [];
        $this->info("Importing pathways");
        $count = Utils::countLines($this->mapFile);
        $fp = fopen($this->mapFile, 'r');
        if (!$fp) {
            $this->error("Unable to read pathways file.");
            return false;
        }
        $bar = $this->output->createProgressBar($count);
        while (($line = fgets($fp)) !== false) {
            $bar->advance();
            $line = str_replace(["\r", "\n"], "", $line);
            if (strlen($line) == 0 || $line{0} == '#') {
                continue;
            }
            $fields = explode("\t", $line);
            if (count($fields) == 4) {
                $pId = (string)$fields[0];
                if (!isset($tmpMap[$pId])) {
                    $tmpMap[$pId] = Pathway::create([
                        'accession'   => $fields[0],
                        'name'        => str_replace(" - Enriched", "", $fields[1]),
                        'organism_id' => $organism->id,
                    ]);
                }
                $startId = $this->nodesMap[(string)$fields[2]];
                $endId = $this->nodesMap[(string)$fields[3]];
                $edgeId = Edge::computeId($startId, $endId, $organism->id);
                if ($tmpMap[$pId]->edges()->find($edgeId) == null) {
                    $tmpMap[$pId]->edges()->attach($edgeId);
                }
                if ($tmpMap[$pId]->nodes()->find($startId) == null) {
                    $tmpMap[$pId]->nodes()->attach($startId);
                }
                if ($tmpMap[$pId]->nodes()->find($endId) == null) {
                    $tmpMap[$pId]->nodes()->attach($endId);
                }
            }
        }
        $bar->finish();
        $this->info("\nDone!");
        return true;
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $organisms = $this->exportOrganisms();
        foreach ($organisms as $accession => $org) {
            if ($this->exportPathways($accession)) {
                if ($this->importNodes()) {
                    if ($this->importEdges($org)) {
                        if (!$this->importPathways($org)) {
                            return 104;
                        }
                    } else {
                        return 103;
                    }
                } else {
                    return 102;
                }
            } else {
                return 101;
            }
        }
        return 0;
    }
}
