library(GEOquery)
library(readr)
library(dplyr)
library(limma)
library(readr)

drug.names <- c('MEKi', 'AKTi', 'STAT3i', 'SRCi', 'mTORi', 'BETi', 'PKCi', 'RAFi', 'JNKi')

inhib <- "UNDEREXPRESSION"
activ <- "OVEREXPRESSION"

drug.targets <- list(
    'MEKi'  =c("5595"=inhib,"5594"=inhib), 
    'RAFi'  =c("5595"=inhib,"5594"=inhib,"6195"=inhib,"5604"=inhib,"5605"=inhib),
    'AKTi'  =c("207"=inhib,"208"=inhib,"2931"=inhib,"2932"=inhib,"6778"=activ,"1956"=activ,"84335"=inhib,"2475"=inhib,"6198"=inhib), 
    'STAT3i'=c("6774"=inhib), 
    'SRCi'  =c("6714"=inhib,"11200"=inhib,"3725"=inhib), 
    'mTORi' =c("6198"=inhib,"1978"=inhib), 
    'BETi'  =c("1026"=activ,"2260"=activ,"1956"=inhib), 
    'PKCi'  =c("1385"=inhib,"3725"=inhib),
    'JNKi'  =c("3725"=inhib,"5599"=inhib,"5601"=inhib)
)

run.phensim <- function(id, sim.gene, sim.dir, non.exp) {
    MITHRIL.COMMAND     <- "C:\\Java\\bin\\java -jar %s/../MITHrIL2.jar phensim -verbose -i %s -e mirna -enrichment-evidence-type STRONG -non-expressed-file %s -number-of-iterations 10 -t 10 -seed 1234 -o %s"
    
    phensim.output.file <- file.path(getwd(), "phensim", paste0("simulation_",id,".txt"))
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
            cat(run.command,"\n")
            stop()
            return (NULL)
        }
        if (!file.exists(phensim.output.file)) {
            cat("An error occurred in PHENSIM. No output produced\n")
            cat(run.command,"\n")
            stop()
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

phensim.data <- vector("list", 0)

for (i in 1:length(drug.names)) {
    for (j in i:length(drug.names)) {
        d1 <- drug.names[i]
        d2 <- drug.names[j]
        cat("Processing",d1,d2,"\n")
        if (d1 == d2) {
            phensim.output <- run.phensim(paste0(d1,"_",d2), names(drug.targets[[d1]]), unname(drug.targets[[d1]]), c())
        } else {
            t1 <- drug.targets[[d1]]
            t2 <- drug.targets[[d2]]
            common <- intersect(names(t1), names(t2))
            if (length(common) > 0 && any(t1[common] != t2[common])) {
                opposite <- names(which(t1[common] != t2[common]))
                t1 <- t1[names(t1) != opposite]
                t2 <- t2[names(t2) != opposite]
            }
            final.targets <- c(t1,t2[!(names(t2) %in% common)])
            phensim.output <- run.phensim(paste0(d1,"_",d2), names(final.targets), unname(final.targets), c())
        }
        phensim.data <- c(phensim.data, setNames(list(phensim.output), paste0(d1,"_",d2)))
    }
}


saveRDS(phensim.data, file = "phensim.results.rds")

