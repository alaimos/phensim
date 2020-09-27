library(GEOquery)
library(readr)
library(dplyr)
library(limma)
library(readr)
library(org.Hs.eg.db)
library(annotate)
datasets <- read_delim("data/dataset_benchmark_FINAL.txt", "\t", escape_double = FALSE, trim_ws = TRUE)
class(datasets) <- "data.frame"

data.cache <- readRDS("data.cache.rds")


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
    #TN   <- sum(table[rest, rest])
    TN   <- sum(diag(table)[-pos])
    return (c(
        PPV=(TP/(TP+FP)),
        SENS=(TP/(TP+FN)),
        SPEC=(TN/(TN+FP))
    ))
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
    map       <- unlist(mapIds(org.Hs.eg.db, names(avg.lfcs), 'SYMBOL', 'ENTREZID'))
    names(avg.lfcs) <- unname(map[names(avg.lfcs)])
    res.file  <- file.path(getwd(),"bionsi",paste0("exp",id,"_result.csv"))
    res.filep <- file.path(getwd(),"bionsi",paste0("exp",id,"_result_parsed.csv"))
    if (!file.exists(res.file)) next()
    tmp <- readLines(res.file)
    writeLines(tmp[1:(which(tmp=="####,####")-1)], res.filep)
    bionsi.output     <- read.csv(res.filep)
    bionsi.output     <- bionsi.output[,-ncol(bionsi.output)]
    bionsi.output$FC  <- log2(bionsi.output[[ncol(bionsi.output)]] + 0.5) - log2(bionsi.output[[2]] + 0.5)
    # bionsi.output$FC[bionsi.output[[ncol(bionsi.output) - 1]] == 5] <- 0
    # bionsi.output$FC[bionsi.output[[ncol(bionsi.output) - 1]] >= 0 & bionsi.output[[ncol(bionsi.output) - 1]] < 5 ] <- -1
    # bionsi.output$FC[bionsi.output[[ncol(bionsi.output) - 1]] > 5 & bionsi.output[[ncol(bionsi.output) - 1]] <= 9 ] <- 1
    all.genes         <- unique(bionsi.output[,1])
    tmp.df            <- unique(bionsi.output[,c(1,ncol(bionsi.output))])
    perts.complete    <- factor(sign(setNames(tmp.df[[2]], tmp.df[[1]])), levels = c(1,-1,0))
    common.all        <- intersect(names(perts.complete), names(avg.lfcs))
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
    unlink(res.filep)
}

saveRDS(mtx.results, file = "bionsi.rds")















