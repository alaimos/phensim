<?php

namespace App\Http\Controllers\Simulation;

use App\Http\Controllers\Controller;
use App\Models\Job;
use App\Models\Pathway;
use App\PHENSIM\ExtendedCollectionEngine;
use App\PHENSIM\Reader;
use Datatables;
use Illuminate\Http\JsonResponse;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class SimulationController extends Controller
{
    /**
     * Create a new controller instance.
     */
    public function __construct()
    {
        $this->middleware('auth');
    }

    private function makeComparator(array $intFields = [], array $doubleFields = []): callable
    {
        return static function (ExtendedCollectionEngine $table, $getColumnName) use ($intFields, $doubleFields) {
            $crit = $table->request->orderableColumns();
            $caseSens = $table->isCaseInsensitive();
            if ($crit) {
                $comparator = static function ($a, $b) use ($caseSens, $crit, $getColumnName, $intFields, $doubleFields) {
                    foreach ($crit as $ord) {
                        $column = $getColumnName($ord['column']);
                        [$first, $second] = (strtolower($ord['direction']) === 'desc') ? [$b, $a] : [$a, $b];
                        if (in_array($column, $intFields, true)) {
                            $f = (int)$first[$column];
                            $s = (int)$second[$column];
                            $cmp = $f <=> $s;
                        } elseif (in_array($column, $doubleFields, true)) {
                            $f = (float)$first[$column];
                            $s = (float)$second[$column];
                            $cmp = $f <=> $s;
                        } elseif ($caseSens) {
                            $cmp = strnatcasecmp($first[$column], $second[$column]);
                        } else {
                            $cmp = strnatcmp($first[$column], $second[$column]);
                        }
                        if ($cmp !== 0) {
                            return $cmp;
                        }
                    }

                    return 0;
                };
                $table->collection = $table->collection->map(
                    static function ($data) {
                        return array_dot($data);
                    }
                )->sort($comparator)->map(
                    static function ($data) {
                        foreach ($data as $key => $value) {
                            unset($data[$key]);
                            array_set($data, $key, $value);
                        }

                        return $data;
                    }
                );
            }
        };
    }

    /**
     * Redirect to the real job viewer
     *
     * @param \App\Models\Job $job
     *
     * @return \Illuminate\View\View
     * @throws \Exception
     */
    public function viewSimulation(Job $job): View
    {
        if (!$job || !$job->exists) {
            abort(404, 'Unable to find the job.');
        }
        if (!$job->canBeRead()) {
            abort(403, 'You are not allowed to view this job');
        }

        return view(
            'jobs.simulation_job.pathway_list',
            [
                'job' => $job,
            ]
        );
    }

    /**
     * Prepare data for the pathways list table
     *
     * @param \App\Models\Job $job
     *
     * @return \Illuminate\Http\JsonResponse
     * @throws \Exception
     */
    public function pathwaysListData(Job $job): JsonResponse
    {
        if (!$job || !$job->exists) {
            abort(404, 'Unable to find the job.');
        }
        if (!$job->canBeRead()) {
            abort(403, 'You are not allowed to view this job');
        }
        /** @var \Yajra\Datatables\Engines\CollectionEngine $table */
        $table = Datatables::of((new Reader($job))->readPathwaysList());
        $table->addColumn(
            'action',
            static function (array $data) use ($job) {
                return view(
                    'jobs.simulation_job.pathway_list_action_column',
                    [
                        'job'  => $job,
                        'data' => $data,
                    ]
                )->render();
            }
        )->editColumn(
            'activityScore',
            static function (array $data) {
                return number_format($data['activityScore'], 4);
            }
        )->editColumn(
            'pValue',
            static function (array $data) {
                if ($data['pValue'] < 0.001) {
                    return '< 0.001';
                }

                return number_format($data['pValue'], 4);
            }
        )->editColumn(
            'FDR',
            static function (array $data) {
                if ($data['FDR'] < 0.001) {
                    return '< 0.001';
                }

                return number_format($data['FDR'], 4);
            }
        )->rawColumns(['action'])->order($this->makeComparator([], ['activityScore', 'FDR']))->removeColumn('LL');

        return $table->make(true);
    }

    /**
     * Download data generated by a PHENSIM simulation
     *
     * @param \App\Models\Job $job
     *
     * @return \Symfony\Component\HttpFoundation\BinaryFileResponse
     */
    public function downloadData(Job $job): BinaryFileResponse
    {
        if (!$job || !$job->exists) {
            abort(404, 'Unable to find the job.');
        }
        if (!$job->canBeRead()) {
            abort(403, 'You are not allowed to view this job');
        }
        $fileName = (new Reader($job))->getOutputFilename();

        return response()->download($fileName, 'phensim-output-' . $job->id . '.tsv');
    }

    /**
     * Download data generated by a PHENSIM simulation
     *
     * @param \App\Models\Job $job
     *
     * @return \Symfony\Component\HttpFoundation\BinaryFileResponse
     */
    public function downloadPathwayData(Job $job): BinaryFileResponse
    {
        if (!$job || !$job->exists) {
            abort(404, 'Unable to find the job.');
        }
        if (!$job->canBeRead()) {
            abort(403, 'You are not allowed to view this job');
        }
        $fileName = (new Reader($job))->getPathwayMatrixOutputFilename();

        return response()->download($fileName, 'phensim-pathway-output-' . $job->id . '.tsv');
    }

    /**
     * Download data generated by a PHENSIM simulation
     *
     * @param \App\Models\Job $job
     *
     * @return \Symfony\Component\HttpFoundation\BinaryFileResponse
     */
    public function downloadNodesData(Job $job): BinaryFileResponse
    {
        if (!$job || !$job->exists) {
            abort(404, 'Unable to find the job.');
        }
        if (!$job->canBeRead()) {
            abort(403, 'You are not allowed to view this job');
        }
        $fileName = (new Reader($job))->getNodesMatrixOutputFilename();

        return response()->download($fileName, 'phensim-nodes-output-' . $job->id . '.tsv');
    }

    /**
     * Redirect to the real job viewer
     *
     * @param \App\Models\Job $job
     * @param string          $pid
     *
     * @return \Illuminate\View\View
     */
    public function viewPathway(Job $job, string $pid): View
    {
        if (!$job || !$job->exists) {
            abort(404, 'Unable to find the job.');
        }
        if (!$job->canBeRead()) {
            abort(403, 'You are not allowed to view this job');
        }
        /** @var Pathway $pathway */
        $pathway = Pathway::whereAccession($pid)->first();
        if (!$pathway || !$pathway->exists) {
            abort(404, 'Unable to find the pathway.');
        }
        $old = (new Reader($job))->makePathwayColoring($pid);
        $header = '#' . $job->getData('organism') . "\tPhensim\n";
        $old['coloring'] = $header . $old['coloring'];

        return view(
            'jobs.simulation_job.pathway_view',
            [
                'job'      => $job,
                'pid'      => $pid,
                'pathway'  => $pathway,
                'coloring' => $old,
            ]
        );
    }

    /**
     * Prepare data for the nodes list table
     *
     * @param \App\Models\Job $job
     * @param string          $pid
     *
     * @return \Illuminate\Http\JsonResponse
     * @throws \Exception
     */
    public function pathwayViewListData(Job $job, string $pid): JsonResponse
    {
        if (!$job || !$job->exists) {
            abort(404, 'Unable to find the job.');
        }
        if (!$job->canBeRead()) {
            abort(403, 'You are not allowed to view this job');
        }
        /** @var \Yajra\Datatables\Engines\CollectionEngine $table */
        $table = Datatables::of((new Reader($job))->readPathway($pid));
        $table->editColumn(
            'targetedBy',
            function (array $data) {
                return $data['isDirectTarget'] ? '' : $this->parseNode((array)$data['targetedBy']);
            }
        )->editColumn(
            'activityScore',
            static function (array $data) {
                return number_format($data['activityScore'], 4);
            }
        )->editColumn(
            'pValue',
            static function (array $data) {
                if ($data['pValue'] < 0.001) {
                    return '< 0.001';
                }

                return number_format($data['pValue'], 4);
            }
        )->editColumn(
            'FDR',
            static function (array $data) {
                if ($data['FDR'] < 0.001) {
                    return '< 0.001';
                }

                return number_format($data['FDR'], 4);
            }
        )->editColumn(
            'nodeId',
            function (array $data) {
                return $this->parseNode($data['nodeId']);
            }
        )->editColumn(
            'isEndpoint',
            static function (array $data) {
                return ($data['isEndpoint'] ? 'Yes' : 'No');
            }
        )->editColumn(
            'isDirectTarget',
            static function (array $data) {
                return ($data['isDirectTarget'] ? 'Yes' : 'No');
            }
        )->rawColumns(['nodeId'])->removeColumn('LL', 'pathwayId', 'pathwayName')
              ->order($this->makeComparator([], ['activityScore', 'FDR']));

        return $table->make(true);
    }


}