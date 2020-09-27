library(GEOquery)
library(readr)
library(dplyr)
library(limma)
library(readr)
datasets <- read_delim("data/dataset_benchmark_FINAL_DEGS.txt", "\t", escape_double = FALSE, trim_ws = TRUE)
class(datasets) <- "data.frame"
datasets$GeneId <- as.character(datasets$GeneId)

data.cache <- readRDS("data.cache.degs.rds")

mtx.results <- matrix(NA, nrow = nrow(datasets), ncol = 6,
                      dimnames = list(NULL,
                                      c("Accuracy", "Predicted.PPV", "Predicted.SENS", "Predicted.SPEC", "NonPredicted.PPV", "NonPredicted.FNR")))


accuracy  <- function (table) {
    return (sum(diag(table))/sum(table))
}

sens.spec <- function (table, pos, neg=((1:nrow(table))[-pos])) {
    n    <- nrow(table)
    rest <- neg #(1:n)[-pos]
    TP   <- table[pos,pos]
    FP   <- sum(table[rest, pos])
    FN   <- sum(table[pos, rest])
    TN   <- sum(diag(table)[-pos])
    PPV  <- (TP/(TP+FP))
    Sens <- (TP/(TP+FN))
    if (is.na(PPV)) PPV <- 1
    if (is.na(Sens)) Sens <- 1
    return (c(
        PPV=PPV,
        SENS=Sens,
        SPEC=(TN/(TN+FP))
    ))
}

run.phensim <- function(id, sim.gene, sim.dir, non.exp) {
    MITHRIL.COMMAND     <- "C:\\Java\\bin\\java -jar %s/MITHrIL2.jar phensim -verbose -i %s -e mirna -enrichment-evidence-type STRONG -non-expressed-file %s -number-of-iterations 10 -t 10 -seed 1234 -o %s"
    phensim.output.file <- file.path(getwd(), "phensim_degs", paste0("simulation_",id,".txt"))
    if (!file.exists(phensim.output.file)) {
        non.exp             <- non.exp[!(non.exp %in% sim.gene)]
        phensim.input       <- paste(sim.gene, sim.dir, sep="\t", collapse = "\n")
        phensim.nonex       <- paste(non.exp, collapse = "\n")
        phensim.input.file  <- tempfile("phensim_input", tmpdir = file.path(getwd(), "temp"), fileext = ".txt")
        phensim.nonex.file  <- tempfile("phensim_nonex", tmpdir = file.path(getwd(), "temp"), fileext = ".txt")
        cat(phensim.input, file = phensim.input.file)
        cat(phensim.nonex, file = phensim.nonex.file)
        run.command <- sprintf(MITHRIL.COMMAND, getwd(), phensim.input.file, phensim.nonex.file, phensim.output.file)
        ecode <- system(run.command, intern = FALSE, wait = TRUE)
        if (ecode != 0) {
            cat("An error occurred in PHENSIM. Exit code: ", ecode, "\n")
            return (NULL)
        }
        if (!file.exists(phensim.output.file)) {
            cat("An error occurred in PHENSIM. No output produced\n")
            return (NULL)
        }
        if (file.exists(phensim.input.file)) unlink(phensim.input.file)
        if (file.exists(phensim.nonex.file)) unlink(phensim.nonex.file)
    }
    phensim.output <- read_delim(phensim.output.file, "\t", escape_double = FALSE, 
                                 col_types = cols(`Direct Targets` = col_character()), 
                                 na = "NA", trim_ws = TRUE)
    phensim.output <- na.omit(phensim.output)
    class(phensim.output) <- "data.frame"
    return (phensim.output)
}

export.subgraph <- function(id, sim.gene) {
    MITHRIL.COMMAND     <- "C:\\Java\\bin\\java -jar %s/MITHrIL2.jar export-subgraph -verbose -i %s -e mirna -enrichment-evidence-type STRONG -o %s"
    phensim.output.file <- file.path(getwd(), "phensim_degs", paste0("subgraph_",id,".txt"))
    if (!file.exists(phensim.output.file)) {
        run.command <- sprintf(MITHRIL.COMMAND, getwd(), paste0(sim.gene, collapse = ","), phensim.output.file)
        ecode <- system(run.command, intern = FALSE, wait = TRUE)
        if (ecode != 0) {
            cat("An error occurred in PHENSIM. Exit code: ", ecode, "\n")
            return (NULL)
        }
        if (!file.exists(phensim.output.file)) {
            cat("An error occurred in PHENSIM. No output produced\n")
            return (NULL)
        }
    }
    phensim.output <- read_delim(phensim.output.file, "\t", escape_double = FALSE, col_names = FALSE, 
                                 col_types = cols(X1 = col_character(), X2 = col_character()), trim_ws = TRUE)
    phensim.output <- na.omit(phensim.output)
    if (nrow(phensim.output) <= 2) return (NULL)
    class(phensim.output) <- "data.frame"
    return (unique(phensim.output[[2]]))
}

for (i in 1:nrow(datasets)) {
    cat("Processing",datasets$GEO_ID[i],"\n")
    id        <- datasets$ID[i]
    cases     <- unlist(strsplit(datasets$Cases[i], ","))
    controls  <- unlist(strsplit(datasets$Controls[i], ","))
    sim.gene  <- unlist(strsplit(datasets$GeneId[i], ","))
    sim.dir   <- datasets$Experiment[i]
    ds        <- data.cache[[i]]
    if(is.null(ds)) next();
    non.exp   <- names(which(ds$non.expr))
    data.norm <- ds$data.norm
    cases     <- intersect(cases, colnames(data.norm))
    controls  <- intersect(controls, colnames(data.norm))
    avg.cases <- apply(data.norm[,cases,drop = FALSE], MARGIN = 1, mean)
    avg.cntrs <- apply(data.norm[,controls,drop = FALSE], MARGIN = 1, mean)
    avg.lfcs  <- avg.cases - avg.cntrs
    real.sim.dir <- character(length(sim.gene))
    real.sim.dir[avg.lfcs[sim.gene] < 0] <- "UNDEREXPRESSION"
    real.sim.dir[avg.lfcs[sim.gene] > 0] <- "OVEREXPRESSION"
    if (any(sim.dir != real.sim.dir) && !any(real.sim.dir == "")) {
        sim.dir <- real.sim.dir
    }
    phensim.output <- run.phensim(id, names(ds$degs), unname(ds$degs), non.exp)
    if (is.null(phensim.output)) next();
    subgraph.genes    <- export.subgraph(id, names(ds$degs))
    if (is.null(subgraph.genes)) next();
    all.genes         <- unique(phensim.output[,3])
    tmp.df            <- unique(phensim.output[,c(3,16)])
    perts.complete    <- factor(sign(setNames(tmp.df[[2]], tmp.df[[1]])), levels = c(1,-1,0))
    common.all        <- intersect(subgraph.genes, intersect(names(perts.complete), names(avg.lfcs)))
    orig.data.all     <- avg.lfcs[common.all]
    perts.complete    <- perts.complete[common.all]
    tmp.orig.data     <- sign(orig.data.all)
    tmp.orig.data[abs(orig.data.all) <= 0.6] <- 0
    orig.data.all     <- factor(tmp.orig.data, levels = c(1,-1,0))
    rm(tmp.orig.data, tmp.df)
    table.all  <- table(real=orig.data.all[common.all], predicted=perts.complete[common.all])
    mtx.results[i,1]   <- accuracy(table.all)
    mtx.results[i,2:4] <- sens.spec(table.all, 1, 2)
    mtx.results[i,5]   <- table.all[3,3] / (table.all[3,3] + table.all[1,3] + table.all[2,3])
    mtx.results[i,6]   <- 1 - (table.all[3,3] / (table.all[3,3] + table.all[3,1] + table.all[3,2]))
}


saveRDS(mtx.results, file = "phensim_degs.rds")














