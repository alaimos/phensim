#!/usr/bin/env Rscript
suppressWarnings({
    if (!require("optparse", quietly = TRUE)) install.packages("optparse", dependencies = TRUE)
    if (!requireNamespace("BiocManager", quietly = TRUE)) {
        install.packages("BiocManager", dependencies = TRUE)
    }
    if (!require("qvalue", quietly = TRUE)) BiocManager::install("qvalue", dependencies = TRUE)
    if (!require("locfdr", quietly = TRUE)) BiocManager::install("locfdr", dependencies = TRUE)
})
suppressPackageStartupMessages({
    library(optparse)
    library(qvalue)
    library(locfdr)
})

option_list <- list(
    make_option(c("-i", "--input"), type="character", default=NULL, help="input file", metavar="character"),
    make_option(c("-o", "--output"), type="character", default=NULL, help="output file", metavar="character"),
    make_option(c("-l", "--locfdr"), type="logical", default=FALSE, help="use locfdr instead of qvalue", action = "store_false")
); 

opt_parser <- OptionParser(option_list=option_list)
opt <- parse_args(opt_parser)

if (opt$help) {
    print_help(opt_parser)
}

if (is.null(opt$input) || !file.exists(opt$input)) {
    print_help(opt_parser)
    stop("Input file is required!", call.=FALSE)
}

if (is.null(opt$output)) {
    print_help(opt_parser)
    stop("Output file is required!", call.=FALSE)
}

check.range <- function (v) {
    v[v < 0] <- 0
    v[v > 1] <- 1
    return (v)
}

simulation.results <- read.table(opt$input, header = TRUE, sep = "\t", stringsAsFactors = FALSE, check.names = FALSE, comment.char = "")
nodes <- unique(simulation.results[,c(3,8)])
paths <- unique(simulation.results[,c(1,12)])
nodes[[2]] <- check.range(nodes[[2]])
paths[[2]] <- check.range(paths[[2]])

if (opt$locfdr) {
    nodes[[2]] <- tryCatch(locfdr(nodes[[2]], plot=0)$fdr, error=function(e) (p.adjust(nodes[[2]], method = "fdr")))
    paths[[2]] <- tryCatch(locfdr(paths[[2]], plot=0)$fdr, error=function(e) (p.adjust(paths[[2]], method = "fdr")))
} else {
    nodes[[2]] <- tryCatch(qvalue(nodes[[2]])$qvalues, error=function(e) (p.adjust(nodes[[2]], method = "fdr")))
    paths[[2]] <- tryCatch(qvalue(paths[[2]])$qvalues, error=function(e) (p.adjust(paths[[2]], method = "fdr")))
}

nodes <- setNames(nodes[[2]], nodes[[1]])
paths <- setNames(paths[[2]], paths[[1]])
simulation.results[[9]]  <- unname(nodes[simulation.results[[3]]])
simulation.results[[13]] <- unname(paths[simulation.results[[1]]])

write.table(simulation.results, file = opt$output, quote = FALSE, append = FALSE, sep = "\t", row.names = FALSE, col.names = TRUE, na = "")

