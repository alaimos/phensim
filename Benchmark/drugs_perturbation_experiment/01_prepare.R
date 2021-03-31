library(tidyr)
library(dplyr)
library(org.Hs.eg.db)

timepoints <- c("10m", "27m", "72m", "3h", "9h", "24h", "48h", "67h")
drug.names <- c('DMSO', 'MEKi', 'AKTi', 'STAT3i', 'SRCi', 'mTORi', 'BETi', 'PKCi', 'RAFi', 'JNKi')
control    <- "DMSO_DMSO"
hs <- org.Hs.eg.db

results <- vector("list", 0)
n <- length(drug.names)
for (i in 1:n) {
    for (j in i:n) {
        d1 <- drug.names[i]
        d2 <- drug.names[j]
        file.name <- file.path("raw_data", paste0("data_", d1, "_", d2, ".csv"))
        df.data <- read.csv(file.name)
        df.data <- df.data %>% 
            mutate(Symbols = strsplit(as.character(Symbols), "|", fixed = TRUE)) %>% 
            unnest(Symbols)
        map <- select(hs, 
               keys = df.data$Symbols,
               columns = c("ENTREZID", "SYMBOL"),
               keytype = "SYMBOL")
        map$ENTREZID[map$SYMBOL == "DUSP4 "] <- "1846"
        map$ENTREZID[map$SYMBOL == "growth"] <- "growth"
        map$ENTREZID[map$SYMBOL == "apoptosis"] <- "apoptosis"
        df.data <- df.data %>% 
            inner_join(map, by=c("Symbols"="SYMBOL")) %>% 
            dplyr::select(!(Symbols)) %>%
            group_by(ENTREZID) %>%
            summarise_all(median)
        
        class(df.data) <- "data.frame"
        rownames(df.data) <- df.data$ENTREZID
        df.data <- df.data[,-which(colnames(df.data) == "ENTREZID")]
        colnames(df.data) <- timepoints
        results <- c(results, setNames(list(df.data), paste0(d1, "_", d2)))
    }
}

control.data <- results[[control]]
drugs.data   <- results[-which(names(results) == control)]

for (i in 1:length(drugs.data)) {
    drugs.data[[i]] <- drugs.data[[i]] - control.data
}

saveRDS(drugs.data, file = "drugs_data.rds")


