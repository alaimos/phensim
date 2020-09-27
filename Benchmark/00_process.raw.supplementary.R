library(org.Hs.eg.db)
library(dplyr)

if (!dir.exists("supplementary/")) {
    download.file("https://phensim.atlas.dmi.unict.it/supplementary.zip", "./supplementary.zip")
    if (!file.exists("supplementary.zip")) stop("Supplementary data file not file")
    unzip("supplementary.zip")
    if (!dir.exists("supplementary/")) stop("Unable to create supplementary data directory")
}

x <-  org.Hs.egENSEMBL
mapped_genes <- mappedkeys(x)
xx <- as.list(x[mapped_genes])
gi <- rep(names(xx), sapply(xx, length))
ei <- unname(unlist(xx))
mapping_df <- data.frame(entrez=gi, ensembl=ei)
rm(xx,x,ei,gi,mapped_genes)

mart_export <- read_delim("supplementary/mart_export.txt", "\t", escape_double = FALSE, trim_ws = TRUE)
colnames(mart_export) <- c("gi", "ensembl", "entrez")
mart_export <- na.omit(mart_export)
mapping_df_trans <- mart_export[,c("entrez", "ensembl")]
rm(mart_export)

make.names.map <- function (nms) {
    library(HGNChelper)
    corr.symb <- checkGeneSymbols(nms, unmapped.as.na = FALSE, species = "human")
    map <- setNames(strsplit(corr.symb$Suggested.Symbol, split = "\\s+///\\s+", perl = TRUE), corr.symb$x)
    df.map <- na.omit(data.frame(name=rep(names(map), sapply(map, length)), name_new=unname(unlist(map))))
    map <- mapIds(org.Hs.eg.db, df.map$name_new, 'ENTREZID', 'SYMBOL', multiVals = "list")
    df.map <- df.map %>% inner_join(na.omit(data.frame(name=rep(names(map), sapply(map, length)), id=unname(unlist(map)))), by = c("name_new"="name"))
    df.map <- df.map[,c("name", "id")]
    return (df.map)
}

## GSE91051
mtx <- read.delim("supplementary/GSE91051/GSE91051_Cuffnorm_CountMatrix.txt.gz", header = TRUE)
colnames(mtx) <- c("ensembl", "GSM2420184","GSM2420185","GSM2420186","GSM2420187","GSM2420188","GSM2420189")
mtx_mapped <- mtx %>% inner_join(mapping_df, by="ensembl") %>% group_by(entrez) %>% select(!(ensembl)) %>% summarise_all(median)
class(mtx_mapped) <- "data.frame"
rownames(mtx_mapped) <- mtx_mapped$entrez
mtx_mapped$entrez <- NULL
write.table(mtx_mapped, file = "supplementary/GSE91051/data_count.tsv", sep = "\t", row.names = TRUE, col.names = TRUE, quote = FALSE)
rm(mtx,mtx_mapped)

## GSE99707
library(readxl)
mtx <- read_excel("supplementary/GSE99707/GSE99707_WTvsRSK2KO_gene_expression_browser.xlsx")
mtx <- mtx[,c(4,10,14,18,22)]
colnames(mtx) <- c("ensembl", "GSM2650741","GSM2650742","GSM2650743","GSM2650744")
mtx_mapped <- mtx %>% inner_join(mapping_df, by="ensembl") %>% group_by(entrez) %>% select(!(ensembl)) %>% summarise_all(median)
class(mtx_mapped) <- "data.frame"
rownames(mtx_mapped) <- mtx_mapped$entrez
mtx_mapped$entrez <- NULL
write.table(mtx_mapped, file = "supplementary/GSE99707/data_count.tsv", sep = "\t", row.names = TRUE, col.names = TRUE, quote = FALSE)
rm(mtx,mtx_mapped)

## GSE100144
s1 <- read.delim("supplementary/GSE100144/GSM2671914_siNC.fpkm.txt.gz", header = TRUE)
s1 <- s1 %>% select("ensembl"=ref_id, fpkm) %>% group_by(ensembl) %>% summarise_all(sum) %>% right_join(mapping_df_trans, by="ensembl") %>% select(!(ensembl)) %>% group_by(entrez) %>% summarise_all(sum)
s2 <- read.delim("supplementary/GSE100144/GSM2671915_siHOX9_2.fpkm.txt.gz", header = TRUE)
s2 <- s2 %>% select("ensembl"=ref_id, fpkm) %>% group_by(ensembl) %>% summarise_all(sum) %>% right_join(mapping_df_trans, by="ensembl") %>% select(!(ensembl)) %>% group_by(entrez) %>% summarise_all(sum)
mtx <- s1 %>% full_join(s2, by="entrez")
class(mtx) <- "data.frame"
mtx[is.na(mtx)] <- 0
rownames(mtx) <- mtx$entrez
mtx$entrez <- NULL
colnames(mtx) <- c("GSM2671914", "GSM2671915")
write.table(mtx, file = "supplementary/GSE100144/data_fpkm.tsv", sep = "\t", row.names = TRUE, col.names = TRUE, quote = FALSE)
rm(mtx, s1, s2)

## GSE100399
library(readxl)
mtx <- read_excel("supplementary/GSE100399/GSE100399_ASO_NC_and_M1_ASO_2.xlsx", col_types = c("text", "numeric", "numeric", "skip"))
colnames(mtx) <- c("ensembl", "GSM2680311","GSM2680312")
mtx_mapped <- mtx %>% inner_join(mapping_df, by="ensembl") %>% group_by(entrez) %>% select(!(ensembl)) %>% summarise_all(median)
class(mtx_mapped) <- "data.frame"
rownames(mtx_mapped) <- mtx_mapped$entrez
mtx_mapped$entrez <- NULL
write.table(mtx_mapped, file = "supplementary/GSE100399/data_fpkm.tsv", sep = "\t", row.names = TRUE, col.names = TRUE, quote = FALSE)
rm(mtx,mtx_mapped)

## GSE112059
library(readr)
mtx1 <- read_delim("supplementary/GSE112059/GSE112059_H_5_H_6_readcount.txt.gz", "\t", escape_double = FALSE, trim_ws = TRUE)
mtx2 <- read_delim("supplementary/GSE112059/GSE112059_H3_H4_readcount.txt.gz", "\t", escape_double = FALSE, col_types = cols(X4 = col_skip(), 
                                                                                                                             X5 = col_skip(), X6 = col_skip(), 
                                                                                                                             X7 = col_skip(), X8 = col_skip(), 
                                                                                                                             X9 = col_skip(), X10 = col_skip()), trim_ws = TRUE)
colnames(mtx1) <- c("ensembl", "GSM3056607","GSM3056608")
colnames(mtx2) <- c("ensembl", "GSM3056605","GSM3056606")
mtx <- mtx1 %>% full_join(mtx2, by = "ensembl")
mtx[is.na(mtx)] <- 0
rm(mtx1, mtx2)
mtx_mapped <- mtx %>% inner_join(mapping_df, by="ensembl") %>% group_by(entrez) %>% select(!(ensembl)) %>% summarise_all(median)
class(mtx_mapped) <- "data.frame"
rownames(mtx_mapped) <- mtx_mapped$entrez
mtx_mapped$entrez <- NULL
write.table(mtx_mapped, file = "supplementary/GSE112059/data_count.tsv", sep = "\t", row.names = TRUE, col.names = TRUE, quote = FALSE)
rm(mtx,mtx_mapped)


## GSE115284
library(readr)
s1 <- read_delim("supplementary/GSE115284/GSM3173587_ML-2-ctrl.txt.gz", "\t", escape_double = FALSE, col_types = cols(locus = col_skip()), trim_ws = TRUE)
s2 <- read_delim("supplementary/GSE115284/GSM3173588_ML-2-FST317.txt.gz", "\t", escape_double = FALSE, col_types = cols(locus = col_skip()), trim_ws = TRUE)
s3 <- read_delim("supplementary/GSE115284/GSM3173589_ML-2-FST344.txt.gz", "\t", escape_double = FALSE, col_types = cols(locus = col_skip()), trim_ws = TRUE)
colnames(s1) <- c("name", "GSM3173587")
colnames(s2) <- c("name", "GSM3173588")
colnames(s3) <- c("name", "GSM3173589")
mtx <- unique(unique(s1 %>% full_join(s2, by="name")) %>% full_join(s3, by="name"))
mtx[is.na(mtx)] <- 0
mtx_mapped <- unique(mtx %>% inner_join(make.names.map(mtx$name), by="name")) %>% group_by(id) %>% select(!(name)) %>% summarise_all(median)
class(mtx_mapped) <- "data.frame"
rownames(mtx_mapped) <- mtx_mapped$id
mtx_mapped$id <- NULL
write.table(mtx_mapped, file = "supplementary/GSE115284/data_tpm.tsv", sep = "\t", row.names = TRUE, col.names = TRUE, quote = FALSE)
rm(mtx,mtx_mapped, s1,s2,s3)

## GSE121258
library(readr)
files        <- list.files("supplementary/GSE121258/", pattern = "*.fpkm_tracking.gz", full.names = TRUE)
sample.names <- sapply(strsplit(basename(files), "_"), function(x) (x[1]))
mtx          <- NULL
for (i in 1:length(files)) {
    s <- read_delim(files[i], "\t", escape_double = FALSE, trim_ws = TRUE)
    s <- s[,c("gene_id", "FPKM")]
    colnames(s) <- c("name", sample.names[i])
    if (is.null(mtx)) {
        mtx <- s
    } else {
        mtx <- unique(mtx %>% full_join(s, by = "name"))
    }
}
mtx[is.na(mtx)] <- 0
mtx_mapped <- unique(mtx %>% inner_join(make.names.map(mtx$name), by="name")) %>% group_by(id) %>% select(!(name)) %>% summarise_all(median)
class(mtx_mapped) <- "data.frame"
rownames(mtx_mapped) <- mtx_mapped$id
mtx_mapped$id <- NULL
write.table(mtx_mapped, file = "supplementary/GSE121258/data_fpkm.tsv", sep = "\t", row.names = TRUE, col.names = TRUE, quote = FALSE)
rm(mtx,mtx_mapped, s, i, files, sample.names)

## GSE121626
s1 <- read.delim("D:/Users/alaim/Desktop/PhensimBenchmark/supplementary/GSE121626/GSE121626_JVM2_UGT2B17_DGE.txt.gz")
s2 <- read.delim("D:/Users/alaim/Desktop/PhensimBenchmark/supplementary/GSE121626/GSE121626_MEC1_UGT2B17_DGE.txt.gz")
s1 <- s1[,c(1, 15:20)]
s2 <- s2[,c(1, 15:20)]
colnames(s1) <- c("ensembl", "GSM3440256","GSM3440257","GSM3440258","GSM3440259","GSM3440260","GSM3440261")
colnames(s2) <- c("ensembl", "GSM3440250","GSM3440251","GSM3440252","GSM3440253","GSM3440254","GSM3440255")
mtx <- unique(s1 %>% full_join(s2, by = "ensembl"))
mtx[is.na(mtx)] <- 0
mtx_mapped <- mtx %>% inner_join(mapping_df, by="ensembl") %>% group_by(entrez) %>% select(!(ensembl)) %>% summarise_all(median)
class(mtx_mapped) <- "data.frame"
rownames(mtx_mapped) <- mtx_mapped$entrez
mtx_mapped$entrez <- NULL
write.table(mtx_mapped, file = "supplementary/GSE121626/data_count.tsv", sep = "\t", row.names = TRUE, col.names = TRUE, quote = FALSE)
rm(mtx,mtx_mapped,s1,s2)

## GSE126332
s1 <- read.delim("supplementary/GSE126332/GSE126332_Control-v-gRNA1.tab.txt.gz")
s2 <- read.delim("supplementary/GSE126332/GSE126332_Control-v-gRNA2.tab.txt.gz")
s1 <- s1[,c(1,3,4)]
s2 <- s2[,c(1,3,4)]
colnames(s1) <- c("ensembl", "GSM3596631", "GSM3596634")
colnames(s2) <- c("ensembl", "GSM3596632", "GSM3596637")
mtx <- unique(s1 %>% full_join(s2, by = "ensembl"))
mtx[is.na(mtx)] <- 0
mtx_mapped <- mtx %>% inner_join(mapping_df, by="ensembl") %>% group_by(entrez) %>% select(!(ensembl)) %>% summarise_all(median)
class(mtx_mapped) <- "data.frame"
rownames(mtx_mapped) <- mtx_mapped$entrez
mtx_mapped$entrez <- NULL
write.table(mtx_mapped, file = "supplementary/GSE126332/data_count.tsv", sep = "\t", row.names = TRUE, col.names = TRUE, quote = FALSE)
rm(mtx,mtx_mapped,s1,s2)

## GSE129340
s1 <- read.delim("supplementary/GSE129340/GSE129340_H209_ASCL1KD_genes.fpkm_tracking.gz")
s2 <- read.delim("supplementary/GSE129340/GSE129340_H209_TTF1KD_genes.fpkm_tracking.gz")
s3 <- read.delim("supplementary/GSE129340/GSE129340_H441_TTF1KD_genes.fpkm_tracking.gz")
s1 <- s1[,c(4,10,14,18)]
s2 <- s2[,c(4,10,14,18)]
s3 <- s3[,c(4,10,14,18)]
colnames(s1) <- c("name", "GSM3711341", "GSM3711342", "GSM3711343")
colnames(s2) <- c("name", "GSM3711344", "GSM3711345", "GSM3711346")
colnames(s3) <- c("name", "GSM3711347", "GSM3711348", "GSM3711349")
mtx <- unique(unique(s1 %>% full_join(s2, by="name")) %>% full_join(s3, by="name"))
mtx[is.na(mtx)] <- 0
mtx_mapped <- unique(mtx %>% inner_join(make.names.map(mtx$name), by="name")) %>% group_by(id) %>% select(!(name)) %>% summarise_all(median)
class(mtx_mapped) <- "data.frame"
rownames(mtx_mapped) <- mtx_mapped$id
mtx_mapped$id <- NULL
write.table(mtx_mapped, file = "supplementary/GSE129340/data_fpkm.tsv", sep = "\t", row.names = TRUE, col.names = TRUE, quote = FALSE)
rm(mtx,mtx_mapped, s1, s2, s3)

## GSE129672
s1 <- read.delim("supplementary/GSE129672/GSE129672_BL41scr-VS-BL41F1sh.DEseq2_Method.GeneDiffExp.xls.gz")
s2 <- read.delim("supplementary/GSE129672/GSE129672_Jiyoyescr-VS-JiyoyeF1sh.DEseq2_Method.GeneDiffExp.xls.gz")
s3 <- read.delim("supplementary/GSE129672/GSE129672_Namscr-VS-NamF1sh.DEseq2_Method.GeneDiffExp.xls.gz")
s1 <- s1[,c(1, 3:7)]
s2 <- s2[,c(1, 3:7)]
s3 <- s3[,c(1, 3:8)]
colnames(s1) <- c("entrez", "GSM3719281", "GSM3719282", "GSM3719283", "GSM3719284", "GSM3719285")
colnames(s2) <- c("entrez", "GSM3719292", "GSM3719293", "GSM3719294", "GSM3719295", "GSM3719296")
colnames(s3) <- c("entrez", "GSM3719286", "GSM3719287", "GSM3719288", "GSM3719289", "GSM3719290", "GSM3719291")
mtx <- unique(unique(s1 %>% full_join(s2, by="entrez")) %>% full_join(s3, by="entrez"))
mtx[is.na(mtx)] <- 0
mtx_mapped <- mtx %>% group_by(entrez) %>% summarise_all(median)
class(mtx_mapped) <- "data.frame"
rownames(mtx_mapped) <- mtx_mapped$entrez
mtx_mapped$entrez <- NULL
write.table(mtx_mapped, file = "supplementary/GSE129672/data_fpkm.tsv", sep = "\t", row.names = TRUE, col.names = TRUE, quote = FALSE)
rm(mtx,mtx_mapped, s1, s2, s3)

## GSE133841
mtx <- read.delim("supplementary/GSE133841/GSE133841_All.counts.txt.gz")
colnames(mtx) <- c("name", "GSM3928239", "GSM3928240", "GSM3928241", "GSM3928242", "GSM3928243", "GSM3928244")
mtx_mapped <- unique(mtx %>% inner_join(make.names.map(mtx$name), by="name")) %>% group_by(id) %>% select(!(name)) %>% summarise_all(median)
class(mtx_mapped) <- "data.frame"
rownames(mtx_mapped) <- mtx_mapped$id
mtx_mapped$id <- NULL
write.table(mtx_mapped, file = "supplementary/GSE133841/data_count.tsv", sep = "\t", row.names = TRUE, col.names = TRUE, quote = FALSE)
rm(mtx,mtx_mapped)

## GSE138938
mtx <- read.delim("supplementary/GSE138938/GSE138938_annotated_combined.counts.txt.gz")
mtx <- mtx[,-32]
colnames(mtx) <- c("ensembl", "GSM4123907", "GSM4123908", "GSM4123909", "GSM4123910", "GSM4123911", 
                   "GSM4123922", "GSM4123923", "GSM4123924", "GSM4123925", "GSM4123926", 
                   "GSM4123917", "GSM4123918", "GSM4123919", "GSM4123920", "GSM4123921", 
                   "GSM4123932", "GSM4123933", "GSM4123934", "GSM4123935", "GSM4123936", 
                   "GSM4123912", "GSM4123913", "GSM4123914", "GSM4123915", "GSM4123916", 
                   "GSM4123927", "GSM4123928", "GSM4123929", "GSM4123930", "GSM4123931")
mtx_mapped <- mtx %>% inner_join(mapping_df, by="ensembl") %>% group_by(entrez) %>% select(!(ensembl)) %>% summarise_all(median)
class(mtx_mapped) <- "data.frame"
rownames(mtx_mapped) <- mtx_mapped$entrez
mtx_mapped$entrez <- NULL
write.table(mtx_mapped, file = "supplementary/GSE138938/data_count.tsv", sep = "\t", row.names = TRUE, col.names = TRUE, quote = FALSE)
rm(mtx,mtx_mapped)

## GSE140198
library(readr)
files        <- list.files("supplementary/GSE140198/", pattern = "*.txt.gz", full.names = TRUE)
sample.names <- sapply(strsplit(basename(files), "_"), function(x) (x[1]))
mtx          <- NULL
for (i in 1:length(files)) {
    s <- read_delim(files[i], "\t", escape_double = FALSE, trim_ws = TRUE)
    colnames(s) <- c("name", sample.names[i])
    s <- s %>% group_by(name) %>% summarise_all(median)
    if (is.null(mtx)) {
        mtx <- s
    } else {
        mtx <- unique(mtx %>% full_join(s, by = "name"))
    }
}
mtx[is.na(mtx)] <- 0
mtx_mapped <- unique(mtx %>% inner_join(make.names.map(mtx$name), by="name")) %>% group_by(id) %>% select(!(name)) %>% summarise_all(median)
class(mtx_mapped) <- "data.frame"
rownames(mtx_mapped) <- mtx_mapped$id
mtx_mapped$id <- NULL
write.table(mtx_mapped, file = "supplementary/GSE140198/data_count.tsv", sep = "\t", row.names = TRUE, col.names = TRUE, quote = FALSE)
rm(mtx,mtx_mapped, s, i, files, sample.names)

## GSE141387
mtx <- read.delim("supplementary/GSE141387/GSE141387_all.gene.rpkm.t1t2t3wt.tsv.gz", na.strings = c("", "-"))
mtx <- mtx[,c(1, seq(from=3, to=33, by=3))]
colnames(mtx) <- c("entrez", "GSM4202173","GSM4202174","GSM4202175","GSM4202176","GSM4202177","GSM4202178",
                   "GSM4202179","GSM4202180","GSM4202181","GSM4202182","GSM4202183")
class(mtx) <- "data.frame"
rownames(mtx) <- mtx$entrez
mtx$entrez <- NULL
mtx[is.na(mtx)] <- 0
write.table(mtx, file = "supplementary/GSE141387/data_count.tsv", sep = "\t", row.names = TRUE, col.names = TRUE, quote = FALSE)
rm(mtx)

## GSE141923
library(readr)
files        <- list.files("supplementary/GSE141923/", pattern = "*.txt.gz", full.names = TRUE)
sample.names <- sapply(strsplit(basename(files), "_"), function(x) (x[1]))
mtx          <- NULL
for (i in 1:length(files)) {
    s <- read_delim(files[i], "\t", escape_double = FALSE, trim_ws = TRUE)
    colnames(s) <- c("name", sample.names[i])
    s <- s %>% group_by(name) %>% summarise_all(median)
    if (is.null(mtx)) {
        mtx <- s
    } else {
        mtx <- unique(mtx %>% full_join(s, by = "name"))
    }
}
mtx[is.na(mtx)] <- 0
mtx_mapped <- unique(mtx %>% inner_join(make.names.map(mtx$name), by="name")) %>% group_by(id) %>% select(!(name)) %>% summarise_all(median)
class(mtx_mapped) <- "data.frame"
rownames(mtx_mapped) <- mtx_mapped$id
mtx_mapped$id <- NULL
write.table(mtx_mapped, file = "supplementary/GSE141923/data_count.tsv", sep = "\t", row.names = TRUE, col.names = TRUE, quote = FALSE)
rm(mtx,mtx_mapped, s, i, files, sample.names)

## GSE142024
mtx <- read.delim("supplementary/GSE142024/GSE142024_raw_counts.txt.gz")
colnames(mtx) <- c("ensembl", "GSM4217775", "GSM4217776", "GSM4217777", "GSM4217778", "GSM4217779", 
                   "GSM4217780")
mtx_mapped <- mtx %>% inner_join(mapping_df, by="ensembl") %>% group_by(entrez) %>% select(!(ensembl)) %>% summarise_all(median)
class(mtx_mapped) <- "data.frame"
rownames(mtx_mapped) <- mtx_mapped$entrez
mtx_mapped$entrez <- NULL
write.table(mtx_mapped, file = "supplementary/GSE142024/data_count.tsv", sep = "\t", row.names = TRUE, col.names = TRUE, quote = FALSE)
rm(mtx,mtx_mapped)

## GSE145180
library(readxl)
mtx <- read_excel("supplementary/GSE145180/GSE145180_FPKM_GAP_CRC.xlsx")
mtx <- mtx[,1:5]
colnames(mtx) <- c("ensembl", "GSM4308117","GSM4308118","GSM4308115","GSM4308116")
mtx_mapped <- mtx %>% inner_join(mapping_df, by="ensembl") %>% group_by(entrez) %>% select(!(ensembl)) %>% summarise_all(median)
class(mtx_mapped) <- "data.frame"
rownames(mtx_mapped) <- mtx_mapped$entrez
mtx_mapped$entrez <- NULL
write.table(mtx_mapped, file = "supplementary/GSE145180/data_fpkm.tsv", sep = "\t", row.names = TRUE, col.names = TRUE, quote = FALSE)
rm(mtx,mtx_mapped)

## GSE146604
library(readxl)
mtx <- read_excel("supplementary/GSE146604/GSE146604_Expression_Transcript_FTSJ1_RZ.xlsx")
colnames(mtx) <- c("ensembl", "GSM4396421","GSM4396422","GSM4396423","GSM4396424", "GSM4396425", "GSM4396426")
mtx$ensembl <- sapply(strsplit(mtx$ensembl, ".", fixed = TRUE), function(x)(x[1]))
mtx_mapped <- mtx %>% inner_join(mapping_df_trans, by="ensembl") %>% group_by(entrez) %>% select(!(ensembl)) %>% summarise_all(median)
class(mtx_mapped) <- "data.frame"
rownames(mtx_mapped) <- mtx_mapped$entrez
mtx_mapped$entrez <- NULL
write.table(mtx_mapped, file = "supplementary/GSE146604/data_fpkm.tsv", sep = "\t", row.names = TRUE, col.names = TRUE, quote = FALSE)
rm(mtx,mtx_mapped)

## GSE146774
library(readr)
files        <- list.files("supplementary/GSE146774/", pattern = "*.txt.gz", full.names = TRUE)
sample.names <- sapply(strsplit(basename(files), "_"), function(x) (x[1]))
mtx          <- NULL
for (i in 1:length(files)) {
    s <- read_delim(files[i], "\t", escape_double = FALSE, trim_ws = TRUE)
    colnames(s) <- c("ensembl", sample.names[i])
    s <- s %>% group_by(ensembl) %>% summarise_all(median)
    if (is.null(mtx)) {
        mtx <- s
    } else {
        mtx <- unique(mtx %>% full_join(s, by = "ensembl"))
    }
}
mtx_mapped <- unique(mtx %>% inner_join(mapping_df, by="ensembl")) %>% group_by(entrez) %>% select(!(ensembl)) %>% summarise_all(median)
class(mtx_mapped) <- "data.frame"
rownames(mtx_mapped) <- mtx_mapped$entrez
mtx_mapped$entrez <- NULL
write.table(mtx_mapped, file = "supplementary/GSE146774/data_fpkm.tsv", sep = "\t", row.names = TRUE, col.names = TRUE, quote = FALSE)
rm(mtx,mtx_mapped, s, i, files, sample.names)

## GSE146982
library(readxl)
mtx1 <- read.delim("supplementary/GSE146982/GSE146982_A549_samples_gene_expression_information.txt.gz")
mtx1 <- mtx1[,c(1, 6:17)]
colnames(mtx1) <- c("ensembl", "GSM4411732", "GSM4411729", "GSM4411733", "GSM4411730", "GSM4411734", "GSM4411731", "GSM4411738", "GSM4411735", 
                    "GSM4411739", "GSM4411736", "GSM4411740", "GSM4411737")
mtx2 <- read_excel("supplementary/GSE146982/GSE146982_786-O_samples_gene_expression_information.xls")
mtx2 <- mtx2[,c(1, 6:17)]
colnames(mtx2) <- c("ensembl", "GSM4411744", "GSM4411745", "GSM4411746", "GSM4411750", "GSM4411751", "GSM4411752", "GSM4411741", "GSM4411742", 
                    "GSM4411743", "GSM4411747", "GSM4411748", "GSM4411749")
mtx <- mtx1 %>% full_join(mtx2, by = "ensembl")
mtx_mapped <- mtx %>% inner_join(mapping_df, by="ensembl") %>% group_by(entrez) %>% select(!(ensembl)) %>% summarise_all(median)
class(mtx_mapped) <- "data.frame"
rownames(mtx_mapped) <- mtx_mapped$entrez
mtx_mapped$entrez <- NULL
write.table(mtx_mapped, file = "supplementary/GSE146982/data_fpkm.tsv", sep = "\t", row.names = TRUE, col.names = TRUE, quote = FALSE)
rm(mtx,mtx_mapped,mtx1,mtx2)

## GSE148551
library(readr)
files        <- list.files("supplementary/GSE148551/", pattern = "*.txt.gz", full.names = TRUE)
sample.names <- sapply(strsplit(basename(files), "_"), function(x) (x[1]))
mtx          <- NULL
for (i in 1:length(files)) {
    s <- read_delim(files[i], "\t", escape_double = FALSE, trim_ws = TRUE)
    s <- s[,c(1,7)]
    colnames(s) <- c("ensembl", sample.names[i])
    s <- s %>% group_by(ensembl) %>% summarise_all(median)
    if (is.null(mtx)) {
        mtx <- s
    } else {
        mtx <- unique(mtx %>% full_join(s, by = "ensembl"))
    }
}
mtx_mapped <- unique(mtx %>% inner_join(mapping_df, by="ensembl")) %>% group_by(entrez) %>% select(!(ensembl)) %>% summarise_all(median)
class(mtx_mapped) <- "data.frame"
rownames(mtx_mapped) <- mtx_mapped$entrez
mtx_mapped$entrez <- NULL
write.table(mtx_mapped, file = "supplementary/GSE148551/data_fpkm.tsv", sep = "\t", row.names = TRUE, col.names = TRUE, quote = FALSE)
rm(mtx,mtx_mapped, s, i, files, sample.names)

## GSE149292
library(readxl)
mtx <- read_excel("supplementary/GSE149292/GSE149292_genes_fpkm_expression.xlsx")
mtx <- mtx[,c(1, 18:21)]
colnames(mtx) <- c("ensembl", "GSM4495671","GSM4495672","GSM4495673","GSM4495674")
mtx_mapped <- mtx %>% inner_join(mapping_df, by="ensembl") %>% group_by(entrez) %>% select(!(ensembl)) %>% summarise_all(median)
class(mtx_mapped) <- "data.frame"
rownames(mtx_mapped) <- mtx_mapped$entrez
mtx_mapped$entrez <- NULL
write.table(mtx_mapped, file = "supplementary/GSE149292/data_fpkm.tsv", sep = "\t", row.names = TRUE, col.names = TRUE, quote = FALSE)
rm(mtx,mtx_mapped)

## GSE150393
library(tximport)
samples <- list.dirs("supplementary/GSE150393/", full.names = FALSE, recursive = FALSE)
files <- file.path("supplementary/GSE150393", samples, "quant.sf")
names(files) <- samples
ensembl94 <- read.delim("D:/Users/alaim/Desktop/PhensimBenchmark/supplementary/GSE150393/ensembl94.txt")
ensembl94 <- ensembl94[,c(4,1)]
colnames(ensembl94) <- c("TXNAME", "GENEID")
txi <- tximport(files, type = "salmon", tx2gene = ensembl94)
mtx <- data.frame(ensembl=rownames(txi$counts), txi$counts)
mtx_mapped <- mtx %>% inner_join(mapping_df, by="ensembl") %>% group_by(entrez) %>% select(!(ensembl)) %>% summarise_all(median)
class(mtx_mapped) <- "data.frame"
rownames(mtx_mapped) <- mtx_mapped$entrez
mtx_mapped$entrez <- NULL
write.table(mtx_mapped, file = "supplementary/GSE150393/data_count.tsv", sep = "\t", row.names = TRUE, col.names = TRUE, quote = FALSE)
rm(mtx,mtx_mapped,ensembl94,samples,files,txi)

## GSE150945
mtx <- read.csv("supplementary/GSE150945/GSE150945_combine.count.txt.gz", sep="")
colnames(mtx) <- c("ensembl", "GSM4561666", "GSM4561667", "GSM4561668", "GSM4561661", "GSM4561662", "GSM4561663", "GSM4561664", "GSM4561665", "GSM4561660")
mtx$ensembl <- sapply(strsplit(mtx$ensembl, ".", fixed = TRUE), function(x)(x[1]))
mtx_mapped <- mtx %>% inner_join(mapping_df, by="ensembl") %>% group_by(entrez) %>% select(!(ensembl)) %>% summarise_all(median)
class(mtx_mapped) <- "data.frame"
rownames(mtx_mapped) <- mtx_mapped$entrez
mtx_mapped$entrez <- NULL
write.table(mtx_mapped, file = "supplementary/GSE150945/data_count.tsv", sep = "\t", row.names = TRUE, col.names = TRUE, quote = FALSE)
rm(mtx,mtx_mapped)






