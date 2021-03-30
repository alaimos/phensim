<?php
/**
 * PHENSIM: Phenotype Simulator
 * @version 2.0.0.2
 * @author  Salvatore Alaimo, Ph.D.
 */

return [

    'java'         => env('JAVA_EXECUTABLE', 'java'),
    'rscript'      => env('RSCRIPT_EXECUTABLE', 'Rscript'),
    'mithril'      => env('MITHRIL_JAR', resource_path('bin/MITHrIL2.jar')),
    'fdr'          => env('COMPUTE_FDR_PATH', resource_path('bin/compute_fdrs.R')),
    'build_graph'  => env('BUILD_GRAPH_PATH', resource_path('bin/build_graph.R')),
    'threads'      => env('PHENSIM_THREADS', 2),
    'proxy_url'    => env('PROXY_URL'),
    'proxy_scheme' => env('PROXY_SCHEMA'),

];
