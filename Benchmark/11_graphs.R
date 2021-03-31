make.graph <- function (phensim, bionsi, xlab="PHENSIM", ylab="BioNSI") {
    # Graphs used only as a quick evaluation
    # boxplot(list(
    #     PHENSIM.Acc=na.omit(phensim$Accuracy), BIONSI.Acc=na.omit(bionsi$Accuracy),
    #     PHENSIM.PPV=na.omit(phensim$Predicted.PPV), BIONSI.PPV=na.omit(bionsi$Predicted.PPV),
    #     PHENSIM.SENS=na.omit(phensim$Predicted.SENS), BIONSI.SENS=na.omit(bionsi$Predicted.SENS),
    #     PHENSIM.SPEC=na.omit(phensim$Predicted.SPEC), BIONSI.SPEC=na.omit(bionsi$Predicted.SPEC),
    #     PHENSIM.N.PPV=na.omit(phensim$NonPredicted.PPV), BIONSI.N.PPV=na.omit(bionsi$NonPredicted.PPV),
    #     PHENSIM.N.FNR=na.omit(phensim$NonPredicted.FNR), BIONSI.N.FNR=na.omit(bionsi$NonPredicted.FNR)
    # ), pars=list(cex.axis=0.5))
    
    d1 <- phensim
    colnames(d1) <- paste0("PHENSIM.",colnames(d1))
    d2 <- bionsi
    colnames(d2) <- paste0("BIONSI.",colnames(d2))
    merged <- cbind(d1, d2)
    rm(d1,d2)
    merged$Dataset    <- 1:nrow(merged)
    
    library(ggplot2)
    library(gridExtra)
    
    p0 <- ggplot(data = merged, aes(x=PHENSIM.Accuracy, y=BIONSI.Accuracy)) + geom_point(aes(color=Dataset)) + 
        xlab(xlab) + ylab(ylab) + ylim(c(0,1)) + xlim(c(0,1)) + geom_segment(aes(x=0, y=0, xend=1, yend=1), linetype=2) + ggtitle("Overall Accuracy")
    
    p1 <- ggplot(data = merged, aes(x=PHENSIM.Predicted.PPV, y=BIONSI.Predicted.PPV)) + geom_point(aes(color=Dataset)) + 
        xlab(xlab) + ylab(ylab) + ylim(c(0,1)) + xlim(c(0,1)) + geom_segment(aes(x=0, y=0, xend=1, yend=1), linetype=2) + ggtitle("Expressed Genes - PPV")
    
    p2 <- ggplot(data = merged, aes(x=PHENSIM.Predicted.SENS, y=BIONSI.Predicted.SENS)) + geom_point(aes(color=Dataset)) + 
        xlab(xlab) + ylab(ylab) + ylim(c(0,1)) + xlim(c(0,1)) + geom_segment(aes(x=0, y=0, xend=1, yend=1), linetype=2) + ggtitle("Expressed Genes - Sensitivity")
    
    p3 <- ggplot(data = merged, aes(x=PHENSIM.Predicted.SPEC, y=BIONSI.Predicted.SPEC)) + geom_point(aes(color=Dataset)) + 
        xlab(xlab) + ylab(ylab) + ylim(c(0,1)) + xlim(c(0,1)) + geom_segment(aes(x=0, y=0, xend=1, yend=1), linetype=2) + ggtitle("Expressed Genes - Specificity")
    
    p4 <- ggplot(data = merged, aes(x=PHENSIM.NonPredicted.PPV, y=BIONSI.NonPredicted.PPV)) + geom_point(aes(color=Dataset)) + 
        xlab(xlab) + ylab(ylab) + ylim(c(0,1)) + xlim(c(0,1)) + geom_segment(aes(x=0, y=0, xend=1, yend=1), linetype=2) + ggtitle("Non-expressed Genes - PPV")
    
    p5 <- ggplot(data = merged, aes(x=PHENSIM.NonPredicted.FNR, y=BIONSI.NonPredicted.FNR)) + geom_point(aes(color=Dataset)) + 
        xlab(xlab) + ylab(ylab) + ylim(c(0,1)) + xlim(c(0,1)) + geom_segment(aes(x=0, y=0, xend=1, yend=1), linetype=2) + ggtitle("Non-expressed Genes - FNR")
    
    pp <- grid.arrange(p0, p1,p2,p3,p4,p5, nrow=3, ncol=2)
    
    return (pp)
}

phensim <- as.data.frame(readRDS("phensim.rds"))
bionsi  <- as.data.frame(readRDS("bionsi.rds"))
pp <- make.graph(phensim, bionsi)
ggsave(filename = "comparison_1.png", plot = pp, width = 25, height = 20, units = "cm", dpi = 600)
rm(pp, phensim, bionsi)


phensim <- as.data.frame(readRDS("phensim_degs.rds"))
bionsi  <- as.data.frame(readRDS("bionsi_degs.rds"))
pp <- make.graph(phensim, bionsi)
ggsave(filename = "comparison_2.png", plot = pp, width = 25, height = 20, units = "cm", dpi = 600)
rm(pp, phensim, bionsi)

phensim_reactome <- as.data.frame(readRDS("phensim_reactome.rds"))
phensim  <- as.data.frame(readRDS("phensim.rds"))
pp <- make.graph(phensim_reactome, phensim, xlab = "PHENSIM with REACTOME", ylab="PHENSIM only KEGG")
ggsave(filename = "comparison_3.png", plot = pp, width = 25, height = 20, units = "cm", dpi = 600)
rm(pp, phensim_reactome, phensim)


phensim_reactome <- as.data.frame(readRDS("phensim_degs_reactome.rds"))
phensim  <- as.data.frame(readRDS("phensim_degs.rds"))
pp <- make.graph(phensim_reactome, phensim, xlab = "PHENSIM with REACTOME", ylab="PHENSIM only KEGG")
ggsave(filename = "comparison_4.png", plot = pp, width = 25, height = 20, units = "cm", dpi = 600)
rm(pp, phensim_reactome, phensim)


