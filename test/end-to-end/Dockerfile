FROM boxproject/box:4.6.10 AS build_pie_phar
RUN apk add git
COPY . /app
RUN cd /app && touch creating_this_means_phar_will_never_be_verified && /box.phar compile

FROM alpine AS test_pie_installs_build_tools_on_alpine
RUN apk add php php-phar php-mbstring php-iconv php-openssl bzip2-dev libbz2
COPY --from=build_pie_phar /app/pie.phar /usr/local/bin/pie
RUN pie install --auto-install-build-tools -v php/bz2
RUN apk del .php-pie-deps
RUN pie show

FROM fedora AS test_pie_installs_build_tools_on_fedora
RUN dnf install -y php php-pecl-zip unzip bzip2-devel
COPY --from=build_pie_phar /app/pie.phar /usr/local/bin/pie
RUN pie install --auto-install-build-tools -v php/bz2
RUN pie show

FROM ubuntu AS test_pie_installs_build_tools_on_ubuntu
RUN apt-get update && apt-get install -y php unzip libbz2-dev
COPY --from=build_pie_phar /app/pie.phar /usr/local/bin/pie
RUN pie install --auto-install-build-tools -v php/bz2
RUN pie show

FROM homebrew/brew AS test_pie_installs_build_tools_with_brew
RUN brew install php
COPY --from=build_pie_phar /app/pie.phar /usr/local/bin/pie
USER root
RUN apt-get update && apt-get install -y unzip libbz2-dev
RUN apt-get remove --allow-remove-essential -y apt
USER linuxbrew
RUN brew install bzip2
RUN pie install --auto-install-build-tools -v php/bz2
RUN pie show
