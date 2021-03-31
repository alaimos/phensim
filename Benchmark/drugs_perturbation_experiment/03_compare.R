library(ggplot2)
library(ggbeeswarm)
library(reshape2)

proteins.exp <- readRDS("drugs_data.rds")
simulations  <- readRDS("phensim.results.rds")
timepoints <- c("10m", "27m", "72m", "3h", "9h", "24h", "48h", "67h")
drug.names <- c('MEKi', 'AKTi', 'STAT3i', 'SRCi', 'mTORi', 'BETi', 'PKCi', 'RAFi', 'JNKi')


mtx.results <- matrix(NA, nrow=length(names(simulations)), ncol = length(timepoints), dimnames = list(names(simulations),timepoints))
for (n in names(simulations)) {
    s <- unique(simulations[[n]][,c("Node Id", "Average Node Perturbation")])
    sim.data <- setNames(s$`Average Node Perturbation`, s$`Node Id`)
    for (t in 1:length(timepoints)) {
        mtx.results[n,t] <- cor(sim.data[rownames(proteins.exp[[n]])], proteins.exp[[n]][,t], use = "pairwise.complete.obs")
    }
}

df.results <- melt(mtx.results)
colnames(df.results) <- c("Combination", "Timepoint", "Correlation")

p <- ggplot(aes(x=Timepoint,y=Correlation, colour=Timepoint), data = df.results) +
    geom_violin()+ geom_dotplot(binaxis='y', stackdir='center',position=position_dodge(1),binwidth = 0.01)
ggsave(p, filename = "results_correlation.png", dpi = 600, units = "cm", width = 18, height = 18)
