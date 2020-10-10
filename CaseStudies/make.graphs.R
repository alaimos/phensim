library(readr)
library(pathview)

make.symmetric.range <- function (rng) {
    if (all(rng <= 0)) {
        min <- rng[1]
        return (c(min,abs(min)))
    } else  if (all(rng >= 0)) {
        max <- rng[2]
        return (c(-max, max))
    } else {
        r <- max(abs(rng))
        return (c(-r,r))
    }
}

plot.pathway <- function (phensim.output, pathway, output.dir, suffix = "graph") {
    curr.wd      <- getwd()
    pathway      <- gsub("path:", "", pathway, fixed = TRUE)
    pathway.data <- phensim.output[phensim.output[[1]] == paste0("path:", pathway),]
    genes        <- suppressWarnings(!is.na(as.numeric(pathway.data[[3]])))
    compounds    <- grepl("^cpd:", pathway.data[[3]], perl = TRUE)
    genes.data   <- setNames(pathway.data[[7]][genes], pathway.data[[3]][genes])
    genes.range  <- make.symmetric.range(range(genes.data))
    cpd.data     <- setNames(pathway.data[[7]][compounds], gsub("cpd:", "", pathway.data[[3]][compounds], fixed = TRUE))
    if (length(cpd.data) == 0) {
        cpd.data  <- NULL
        cpd.range <- 1
    } else {
        cpd.range <- make.symmetric.range(range(cpd.data))
    }
    if (!dir.exists(file.path(curr.wd, "tmp"))) dir.create(file.path(curr.wd, "tmp"), recursive = TRUE)
    setwd(output.dir)
    pv.out       <- pathview(gene.data = genes.data, cpd.data = cpd.data, pathway.id = pathway, species = "hsa", out.suffix = suffix,
                             limit = list(gene=genes.range,cpd=cpd.range), bins=c(gene=20, cpd=20),
                             kegg.native = TRUE, low = list(gene = "blue", cpd = "blue"), 
                             mid = list(gene = "white", cpd = "white"), high = list(gene = "red", cpd ="red"),
                             plot.col.key = FALSE, kegg.dir = file.path(curr.wd, "tmp"))
    setwd(curr.wd)
}

phensim.output <- read_delim("Case Studies/Simulation #1/output.tsv", "\t", escape_double = FALSE, col_types = cols(`Direct Targets` = col_character()), trim_ws = TRUE)
plot.pathway(phensim.output, "hsa04150", "Case Studies/Simulation #1/")
plot.pathway(phensim.output, "hsa04668", "Case Studies/Simulation #1/")

phensim.output <- read_delim("Case Studies/Simulation #2/output.tsv", "\t", escape_double = FALSE, col_types = cols(`Direct Targets` = col_character()), trim_ws = TRUE)
plot.pathway(phensim.output, "hsa04150", "Case Studies/Simulation #2/")

phensim.output <- read_delim("Case Studies/Simulation #3/output.tsv", "\t", escape_double = FALSE, col_types = cols(`Direct Targets` = col_character()), trim_ws = TRUE)
plot.pathway(phensim.output, "hsa04380", "Case Studies/Simulation #3/")
plot.pathway(phensim.output, "hsa04060", "Case Studies/Simulation #3/")
plot.pathway(phensim.output, "hsa04151", "Case Studies/Simulation #3/")

phensim.output <- read_delim("Case Studies/Simulation #4/output_HELA.tsv", "\t", escape_double = FALSE, col_types = cols(`Direct Targets` = col_character()), trim_ws = TRUE)
plot.pathway(phensim.output, "hsa04210", "Case Studies/Simulation #4/", suffix = "graph_HELA")
plot.pathway(phensim.output, "hsa04668", "Case Studies/Simulation #4/", suffix = "graph_HELA")

phensim.output <- read_delim("Case Studies/Simulation #4/output_RKO.tsv", "\t", escape_double = FALSE, col_types = cols(`Direct Targets` = col_character()), trim_ws = TRUE)
plot.pathway(phensim.output, "hsa04210", "Case Studies/Simulation #4/", suffix = "graph_RKO")
plot.pathway(phensim.output, "hsa04668", "Case Studies/Simulation #4/", suffix = "graph_RKO")
