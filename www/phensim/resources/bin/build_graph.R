#!/usr/bin/env Rscript
suppressWarnings({
  if (!require("optparse", quietly = TRUE)) install.packages("optparse", dependencies = TRUE)
  if (!requireNamespace("BiocManager", quietly = TRUE)) {
    install.packages("BiocManager", dependencies = TRUE)
  }
  if (!require("rjson", quietly = TRUE)) BiocManager::install("rjson", dependencies = TRUE)
  if (!require("dplyr", quietly = TRUE)) BiocManager::install("dplyr", dependencies = TRUE)
  if (!require("SBGNview", quietly = TRUE)) BiocManager::install("SBGNview", dependencies = TRUE)
  if (!require("pathview", quietly = TRUE)) BiocManager::install("pathview", dependencies = TRUE)
})
suppressPackageStartupMessages({
  library(rjson)
  library(optparse)
  library(dplyr)
  library(SBGNview)
  library(SBGNview.data)
  library(pathview)
})

option_list <- list(
  make_option(c("-i", "--input"), type="character", default=NULL, help="input file", metavar="character"),
  make_option(c("-p", "--pathway"), type="character", default=NULL, help="input pathway", metavar="character"),
  make_option(c("-g", "--organism"), type="character", default=NULL, help="input organism", metavar="character"),
  make_option(c("-o", "--output"), type="character", default=NULL, help="output file", metavar="character")
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

input.data <- fromJSON(file = opt$input)

input.vector <- sapply(input.data$data, function(x)(return(setNames(x$activityScore, x$nodeId))))
df.data      <- data.frame(id=names(input.vector), activity=unname(input.vector), stringsAsFactors = FALSE)
df.data$type <- "gene"
df.data$type[grep("miR-|let-", df.data$id, perl = TRUE)] <- "mirna"
df.data$type[grep("^cpd:", df.data$id, perl = TRUE)] <- "compound"
df.data$type[grep("^chebi:", df.data$id, perl = TRUE)] <- "chebi"
df.data$id[df.data$type == "compound"] <- gsub("cpd:", "", df.data$id[df.data$type == "compound"])
genes.vector <- setNames(df.data$activity[df.data$type == "gene"], df.data$id[df.data$type == "gene"])
max.genes <- ceiling(abs(max(genes.vector)))
if (length(which(df.data$type == "compound")) > 0) {
  metab.vector <- setNames(df.data$activity[df.data$type == "compound"], df.data$id[df.data$type == "compound"])
  max.metab <- ceiling(abs(max(metab.vector)))
} else {
  metab.vector <- NULL
  max.metab <- 1
}
if (max.metab == 0) max.metab <- 1
if (max.genes == 0) max.genes <- 1

if (substr(opt$pathway,1,5) == "path:") {
  old.wd <- getwd()
  setwd(tempdir())
  suffix <- basename(tempfile("pathview_"))
  pathview(gene.data = genes.vector, cpd.data = metab.vector, 
           pathway.id = gsub("path:", "", opt$pathway), 
           kegg.dir = tempdir(), 
           out.suffix = suffix,
           limit = list(gene = c(-max.genes,max.genes), cpd=c(-max.metab, max.metab)),
           kegg.native = TRUE, 
           low = list(gene = "blue", cpd = "blue"), 
           mid = list(gene = "white", cpd = "white"), 
           high = list(gene = "red", cpd = "red"))
  files <- list.files(".")
  tmp.output <- grep(suffix, files)
  if (length(tmp.output) == 0) {
    q(save = "no", status = 102)
  }
  file.copy(files[tmp.output], opt$output)
  if (!file.exists(opt$output)) {
    q(save = "no", status = 103)
  }
  unlink(tmp.output)
  setwd(old.wd)
} else {
  data("pathways.info")
  data("sbgn.xmls")
  if (!opt$pathway %in% pathways.info$pathway.id) {
    q(save = "no", status = 101)
  }
  
  output.tmp <- tempfile("react_temp_file_")
  
  obj <- SBGNview(gene.data = genes.vector, 
                  gene.id.type = "entrez",
                  input.sbgn = opt$pathway,
                  cpd.data = metab.vector,
                  cpd.id.type = "kegg",
                  node.sum = "max",
                  output.file = output.tmp,
                  output.formats = "png",
                  min.gene.value = -max.genes,
                  max.gene.value = max.genes,
                  mid.gene.value = 0,
                  min.cpd.value = -max.metab,
                  max.cpd.value = max.metab,
                  mid.cpd.value = 0,
                  sbgn.dir = tempdir())
  print(obj)
  
  files <- list.files(dirname(output.tmp))
  files <- files[nchar(files) > nchar(basename(output.tmp))]
  files <- files[which(substr(files, 1, nchar(basename(output.tmp))) == basename(output.tmp))]
  if (length(grep(".png$", files)) > 0) {
    output.file <- file.path(dirname(output.tmp),files[grep(".png$", files)])
  } else {
    if (length(files) > 0) {
      unlink(file.path(dirname(output.tmp), files))
    }
    q(save = "no", status = 102)
  }
  if (!file.exists(output.file)) {
    q(save = "no", status = 102)
  }
  file.copy(output.file, opt$output, overwrite = TRUE)
  unlink(file.path(dirname(output.tmp), files))
  if (!file.exists(opt$output)) {
    q(save = "no", status = 103)
  }
}
