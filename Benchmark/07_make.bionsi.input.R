library(GEOquery)
library(readr)
library(dplyr)
library(limma)
library(readr)
library(org.Hs.eg.db)
library(annotate)
datasets <- read_delim("data/dataset_benchmark_FINAL.txt", "\t", escape_double = FALSE, trim_ws = TRUE)
class(datasets) <- "data.frame"
meta.nodes <- read_delim("metapathway/nodes.txt", "\t", escape_double = FALSE, trim_ws = TRUE)
colnames(meta.nodes)[1] <- "Id"

data.cache <- readRDS("data.cache.rds")

norm <- 5
over <- 9
undr <- 1

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
    rng <- range(avg.cntrs)
    tmp <- ((avg.cntrs - rng[1]) / (rng[2] - rng[1])) * 8
    map <- unlist(mapIds(org.Hs.eg.db, names(tmp), 'SYMBOL', 'ENTREZID'))
    tmp[tmp != norm] <- norm
    if (length(sim.dir) < length(sim.gene)) {
        sim.dir <- rep(sim.dir, length(sim.gene))
    }
    for (j in 1:length(sim.gene)) {
        if (sim.gene[j] %in% names(tmp)) {
            tmp[sim.gene[j]] <- ifelse(sim.dir[j] == "OVEREXPRESSION", over, undr)
        }
    }
    tmp <- tmp[intersect(meta.nodes$Id, names(tmp))]
    df.tmp <- na.omit(
        data.frame("Gene Symbol"=unname(map[names(tmp)]), exp=unname(tmp), check.names = FALSE)
    )
    df.tmp[nrow(df.tmp) + 1, ] <- list("FAKE_MAX", 9) #BIONSI normalizes between 0 and 9 even if the range of values is already between 0 and 9
    df.tmp[nrow(df.tmp) + 1, ] <- list("FAKE_MIN", 0) #BIONSI normalizes between 0 and 9 even if the range of values is already between 0 and 9
    df.tmp <- unique(df.tmp)
    write.table(df.tmp, file = file.path(getwd(), "bionsi", paste0("exp",id,".csv")), sep = ",", quote = FALSE, row.names = FALSE, col.names = TRUE)
}



















