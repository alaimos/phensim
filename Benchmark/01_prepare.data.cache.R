library(GEOquery)
library(readr)
library(dplyr)
library(limma)
library(readr)

datasets <- read_delim("data/dataset_benchmark_FINAL.txt", "\t", escape_double = FALSE, trim_ws = TRUE)
class(datasets) <- "data.frame"
datasets$GeneId <- as.character(datasets$GeneId)

meta.nodes <- read_delim("metapathway/nodes.txt", "\t", escape_double = FALSE, trim_ws = TRUE)
colnames(meta.nodes)[1] <- "Id"

meta.nodes.id <- unique(meta.nodes$Id)

data.cache <- vector(mode = "list", length = nrow(datasets))

get.dataset <- function(ds, cases, controls) {
    
    gse       <- getGEO(ds, getGPL = FALSE)
    samples      <- lapply(gse, function(s) (sampleNames(phenoData(s))))
    cases.set    <- which(sapply(samples, function(s) (any(cases %in% s))))
    controls.set <- which(sapply(samples, function(s) (any(controls %in% s))))
    keep.sets    <- union(unname(cases.set), unname(controls.set))
    empty.sets   <- all(sapply(gse[keep.sets], function(s)(nrow(exprs(s)) == 0)))
    
    if (empty.sets) {
        file.count <- file.path("supplementary", ds, "data_count.tsv")
        file.fpkm  <- file.path("supplementary", ds, "data_fpkm.tsv")
        file.tpm   <- file.path("supplementary", ds, "data_tpm.tsv")
        if (!file.exists(file.count) && !file.exists(file.fpkm) && !file.exists(file.tpm)) {
            cat("Dataset",ds,"does not contain any data!\n");
            return (NULL)
        } else {
            if (file.exists(file.count)) {
                table    <- read.table(file.count, header = TRUE, sep="\t", stringsAsFactors = FALSE);
                type     <- "count"
            } else if (file.exists(file.fpkm)) {
                table    <- read.table(file.fpkm, header = TRUE, sep="\t", stringsAsFactors = FALSE);
                type     <- "fpkm"
            } else {
                table    <- read.table(file.tpm, header = TRUE, sep="\t", stringsAsFactors = FALSE);
                type     <- "tpm"
            }
            cases    <- table[,intersect(colnames(table), cases),drop = FALSE]
            controls <- table[,intersect(colnames(table), controls),drop = FALSE]
            tmp <- cbind(cases, controls)
            th <- max(1,quantile(data.matrix(tmp), 0.10))
            if (type == "count") {
                non.expr <- apply(tmp, 1, function (x) (mean(x) <= 10))
            } else {
                non.expr <- apply(tmp, 1, function (x) (mean(x) <= th))
            }
            th1 <- max(1,quantile(data.matrix(cases), 0.10))
            if (type == "count") {
                non.expr.cases <- apply(cases, 1, function (x) (mean(x) <= 10))
            } else {
                non.expr.cases <- apply(cases, 1, function (x) (mean(x) <= th1))
            }
            return (list(data=tmp,non.expr=non.expr,non.expr.cases=non.expr.cases,type=type,th=th))
        }
    } else {
        annotations <- lapply(gse[keep.sets], function (s) (Table(getGEO(annotation(s)))))
        annotations.tables <- lapply(annotations, function (t) {
            colnames(t) <- tolower(colnames(t))
            ids  <- t$id
            if (!("entrez_gene_id" %in% colnames(t))) {
                print(colnames(t))
                return (NULL)
            }
            t$entrez_gene_id <- as.character(t$entrez_gene_id)
            eids <- strsplit(t$entrez_gene_id, "\\s+///\\s+", perl = TRUE)
            ids  <- rep(ids, sapply(eids, length))
            eids <- unlist(eids)
            return (data.frame(spot=ids, entrez=eids))
        })
        expressions.tables <- lapply(gse[keep.sets], function(s){
            e <- exprs(s)
            return (data.frame(spot=rownames(e), e, row.names = NULL))
        })
        mapped.expressions.tables <- mapply(function (e,a) {
            if (is.null(a)) return(NULL)
            t <- e %>% dplyr::inner_join(a, by="spot") %>% dplyr::group_by(entrez) %>% dplyr::select(!(spot)) %>% dplyr::summarise_all(median)
            t <- t[!is.na(t$entrez),]
            return (t)
        }, expressions.tables, annotations.tables, SIMPLIFY = FALSE)
        fn.get.samples <- function(t,s) {
            if (is.null(t)) return (NULL)
            cols <- intersect(colnames(t), s)
            if (length(cols) > 0) {
                return (t[,c("entrez", cols),drop=FALSE])
            }
        }
        fn.merge.tables <- function(t1,t2) {
            if (is.null(t1)) return(t2)
            if (is.null(t2)) return(t1)
            merged <- unique(t1 %>% dplyr::full_join(t2, by="entrez"))
            merged[is.na(merged)] <- 0
            merged <- merged %>% dplyr::group_by(entrez) %>% dplyr::summarise_all(median)
            return (merged)
        }
        cases.tables <- lapply(mapped.expressions.tables, fn.get.samples, cases)
        controls.tables <- lapply(mapped.expressions.tables, fn.get.samples, controls)
        df.cases <- NULL
        df.controls <- NULL
        for (i in 1:length(cases.tables)) { 
            df.cases <- fn.merge.tables(df.cases, cases.tables[[i]])
        }
        for (i in 1:length(controls.tables)) { 
            df.controls <- fn.merge.tables(df.controls, controls.tables[[i]])
        }
        if (is.null(df.cases) || is.null(df.controls)) {
            cat("Unable to process data!\n")
            return (NULL)
        }
        df.all <- fn.merge.tables(df.cases, df.controls)
        class(df.all) <- "data.frame"
        rownames(df.all) <- df.all$entrez
        df.all$entrez <- NULL
        cases    <- df.all[,intersect(colnames(df.all), cases),drop = FALSE]
        controls <- df.all[,intersect(colnames(df.all), controls),drop = FALSE]
        tmp <- cbind(cases, controls)
        th <- max(1,quantile(data.matrix(tmp), 0.10))
        non.expr <- apply(tmp, 1, function (x) (mean(x) <= th))
        th1 <- max(1,quantile(data.matrix(cases), 0.10))
        non.expr.cases <- apply(cases, 1, function (x) (mean(x) <= th))
        return (list(data=tmp,non.expr=non.expr,non.expr.cases=non.expr.cases,type="geo",th=th))
    }

}

for (i in 1:nrow(datasets)) {
    cat("Processing",datasets$GEO_ID[i],"\n")
    cases     <- unlist(strsplit(datasets$Cases[i], ","))
    controls  <- unlist(strsplit(datasets$Controls[i], ","))
    sim.gene  <- unlist(strsplit(datasets$GeneId[i], ","))
    sim.dir   <- datasets$Experiment[i]
    ds <- get.dataset(datasets$GEO_ID[i], cases, controls)
    if (ds$type == "count") {
        ds$data.norm <- voom(data.matrix(ds$data))$E
    } else {
        ds$data.norm <- log2(data.matrix(ds$data)+0.5)
    }
    if (ncol(ds$data.norm) > 2) {
        metadata <- data.frame(type = c(rep("case", length(cases)), rep("control", length(controls))), row.names = c(cases, controls))
        metadata <- metadata[colnames(ds$data.norm),,drop=FALSE]
        design   <- model.matrix(~ 0 + type, data = metadata)
        colnames(design) <- c("case", "control")
        fit      <- eBayes(lmFit(ds$data.norm, design))
        cntrs    <- makeContrasts(case-control, levels = design)
        fit2     <- eBayes(contrasts.fit(fit, cntrs))
        degtbl   <- topTable(fit2, coef = 1, number = Inf, adjust.method = "none", p.value = 0.05, lfc = 0.6)
        degtbl   <- degtbl[intersect(rownames(degtbl), meta.nodes.id),]
        if (nrow(degtbl) > 0) {
            tmp <- character(nrow(degtbl))
            tmp[degtbl$logFC < 0] <- "UNDEREXPRESSION"
            tmp[degtbl$logFC > 0] <- "OVEREXPRESSION"
            names(tmp) <- rownames(degtbl)
        } else {
            tmp <- NULL
        }
    } else {
        tmp <- NULL
    }
    ds$degs <- tmp
    data.cache[[i]] <- ds
}

saveRDS(data.cache, file = "data.cache.rds")















