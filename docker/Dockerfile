FROM ubuntu:20.04

ARG WWWGROUP

WORKDIR /var/www/html

ENV DEBIAN_FRONTEND noninteractive
ENV TZ=UTC

RUN ln -snf /usr/share/zoneinfo/$TZ /etc/localtime && echo $TZ > /etc/timezone

RUN apt-get update \
    && apt-get install -y gnupg gosu curl ca-certificates zip unzip git supervisor sqlite3 libcap2-bin libpng-dev python2 \
    && mkdir -p ~/.gnupg \
    && chmod 600 ~/.gnupg \
    && echo "disable-ipv6" >> ~/.gnupg/dirmngr.conf \
    && apt-key adv --homedir ~/.gnupg --keyserver hkp://keyserver.ubuntu.com:80 --recv-keys E5267A6C \
    && apt-key adv --homedir ~/.gnupg --keyserver hkp://keyserver.ubuntu.com:80 --recv-keys C300EE8C \
    && echo "deb http://ppa.launchpad.net/ondrej/php/ubuntu focal main" > /etc/apt/sources.list.d/ppa_ondrej_php.list \
    && apt-get update \
    && apt-get install -y php8.0-cli php8.0-dev \
       php8.0-pgsql php8.0-sqlite3 php8.0-gd \
       php8.0-curl php8.0-memcached \
       php8.0-imap php8.0-mysql php8.0-mbstring \
       php8.0-xml php8.0-zip php8.0-bcmath php8.0-soap \
       php8.0-intl php8.0-readline \
       php8.0-msgpack php8.0-igbinary php8.0-ldap \
       php8.0-redis libapache2-mod-php8.0 apache2 \
    && apt-get autoremove -y \
    && echo "ServerName localhost" >> /etc/apache2/apache2.conf \
    && a2enmod rewrite \
    && sed -i 's/www-data/phensim/' /etc/apache2/envvars \
    && php -r "readfile('http://getcomposer.org/installer');" | php -- --install-dir=/usr/bin/ --filename=composer \
    && curl -sL https://deb.nodesource.com/setup_15.x | bash - \
    && apt-get install -y nodejs \
    && curl -sS https://dl.yarnpkg.com/debian/pubkey.gpg | apt-key add - \
    && echo "deb https://dl.yarnpkg.com/debian/ stable main" > /etc/apt/sources.list.d/yarn.list \
    && apt-get update \
    && apt-get install -y yarn \
    && apt-get install -y mysql-client \
    && apt-get install -y postgresql-client \
    && apt-get install -y openjdk-13-jre \
    && apt-get install -y perl dialog software-properties-common \
    && apt-key adv --keyserver keyserver.ubuntu.com --recv-keys 51716619E084DAB9 \
    && apt-key adv --keyserver keyserver.ubuntu.com --recv-keys E298A3A825C0D65DFD57CBB651716619E084DAB9 \
    && add-apt-repository 'deb https://cloud.r-project.org/bin/linux/ubuntu focal-cran40/' \
    && apt-get install -y r-base-dev \
    && apt-get install -y libxml2-dev libssl-dev libcurl4-openssl-dev librsvg2-dev libmariadbclient-dev libpq-dev \
    && R -e "install.packages(c('optparse', 'BiocManager'), dependencies = TRUE)" \
    && R -e "BiocManager::install(c('qvalue', 'locfdr', 'rjson', 'dplyr', 'SBGNview', 'pathview'), dependencies = TRUE)" \
    && apt-get -y autoremove \
    && apt-get clean \
    && rm -rf /var/lib/apt/lists/* /tmp/* /var/tmp/*

RUN setcap "cap_net_bind_service=+ep" /usr/bin/php8.0

RUN groupadd --force -g $WWWGROUP phensim
RUN useradd -ms /bin/bash --no-user-group -g $WWWGROUP -u 1337 phensim

COPY start-container      /usr/local/bin/start-container
COPY supervisord.conf     /etc/supervisor/conf.d/supervisord.conf
COPY apache_default.conf  /etc/apache2/sites-available/000-default.conf
COPY php.ini              /etc/php/8.0/cli/conf.d/99-phensim.ini
COPY php.ini              /etc/php/8.0/apache2/conf.d/99-phensim.ini
COPY start-apache2.sh     /usr/local/bin/start-apache2.sh
RUN chmod +x /usr/local/bin/start-container
RUN chmod +x /usr/local/bin/start-apache2.sh

EXPOSE 80

ENTRYPOINT ["start-container"]
