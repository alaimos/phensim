#!/usr/bin/env bash

export DEBIAN_FRONTEND=noninteractive
# Add the cran2deb4ubuntu PPA
add-apt-repository -y ppa:marutter/rrutter  
add-apt-repository -y ppa:marutter/c2d4u  

# Make sure the system is up-to-date
apt-get update  

# Install development dependencies, dependencies needed for --check-as-cran,
# or libraries that R packages use (libcurl, gdal)
apt-get install -y build-essential r-base  
apt-get install -y build-essential python3-dev  
apt-get install -y \
  cloc \
  gdal-bin \
  gdebi-core \
  libcurl4-openssl-dev \
  libgdal-dev \
  libproj-dev \
  libxml2-dev \
  libxml2-dev qpdf \
  pandoc \
  python3-pip \
  texinfo \
  texlive-fonts-recommended \
  texlive-humanities \
  texlive-latex-extra  

# Install base R
apt-get install -y r-base r-base-dev  

# Install binary R packages 
apt-get install -y \
  r-cran-bh \
  r-cran-caret \
  r-cran-classint \
  r-cran-crayon \
  r-cran-devtools \
  r-cran-dplyr \
  r-cran-dygraphs \
  r-cran-ggmap \
  r-cran-ggplot2 \
  r-cran-httr \
  r-cran-jsonlite \
  r-cran-knitr \
  r-cran-lubridate \
  r-cran-magrittr \
  r-cran-maptools \
  r-cran-microbenchmark \
  r-cran-pander \
  r-cran-rcpp \
  r-cran-readxl \
  r-cran-rgdal \
  r-cran-rgeos \
  r-cran-rjava \
  r-cran-roxygen2 \
  r-cran-rmarkdown \
  r-cran-rvest \
  r-cran-shiny \
  r-cran-stringdist \
  r-cran-testthat \
  r-cran-tidyr \
  r-cran-xml2 \
  r-cran-xts \
  r-cran-yaml \
  r-cran-zoo  

# Install R packages
Rscript /vagrant/R/install-r-packages.R

# Clean up
apt-get clean -y  
