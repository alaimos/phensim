<?php

namespace App\Http\Controllers;

use App\Models\Node;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Routing\Controller as BaseController;

class Controller extends BaseController
{
    use AuthorizesRequests, DispatchesJobs, ValidatesRequests;

    /**
     * Parse a list of nodes
     *
     * @param array|string $nodes
     *
     * @return string
     * @throws \Throwable
     */
    protected function parseNode($nodes): string
    {
        if (empty($nodes)) return '';
        static $nodeMap = [];
        if (is_array($nodes)) {
            $tmp = [];
            foreach ($nodes as $n) {
                $tmp[] = $this->parseNode($n);
            }
            return implode(', ', array_filter($tmp));
        } else {
            if (isset($nodeMap[$nodes])) {
                return $nodeMap[$nodes];
            }
            if (($n = Node::whereAccession($nodes)->first()) != null) {
                return ($nodeMap[$nodes] = view('common.node_view', ['node' => $n])->render());
            } else {
                return ($nodeMap[$nodes] = view('common.node_view', ['node' => null, 'id' => $nodes])->render());
            }
        }
    }

}
