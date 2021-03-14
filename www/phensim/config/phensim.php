<?php

return [

    'java'    => env('JAVA_EXECUTABLE', 'java'),
    'rscript' => env('RSCRIPT_EXECUTABLE', 'Rscript'),
    'mithril' => env('MITHRIL_JAR', resource_path('bin/MITHrIL2.jar')),
    'fdr'     => env('COMPUTE_FDR_PATH', resource_path('bin/compute_fdrs.R')),
    'threads' => env('PHENSIM_THREADS', 2),

];
