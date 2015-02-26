#!/usr/bin/env bash

# Copyright (c) 2010-2015 Ross Smith II. MIT Licensed.

WGET?=wget --no-check-certificate --tries 99999
MYSQL?=mysql

EMPLOYEES_DB_CODE_TAR_BZ2_URL=https://launchpadlibrarian.net/24493350/employees_db-code-1.0.6.tar.bz2
EMPLOYEES_DB_DUMP_FILES_TAR_BZ2_URL=https://launchpadlibrarian.net/24493789/employees_db-dump-files-1.0.5.tar.bz2
FILL_HELP_TABLES_GZ_URL=http://downloads.mysql.com/docs/fill_help_tables-5.7.sql.gz
SAKILA_DB_TAR_GZ_URL=http://downloads.mysql.com/docs/sakila-db.tar.gz
SAKILA_SPATIAL_DB_TAR_GZ_URL=http://downloads.mysql.com/docs/sakila-spatial-db.tar.gz
WORLD_SQL_GZ_URL=http://downloads.mysql.com/docs/world.sql.gz
# unable to download:
#MENAGERIE_DB_TAR_GZ_URL=http://downloads.mysql.com/docs/menagerie-db.tar.gz

EMPLOYEES_DB_CODE_TAR_BZ2=$(shell basename ${EMPLOYEES_DB_CODE_TAR_BZ2_URL})
EMPLOYEES_DB_DUMP_FILES_TAR_BZ2=$(shell basename ${EMPLOYEES_DB_DUMP_FILES_TAR_BZ2_URL})
FILL_HELP_TABLES_GZ=$(shell basename ${FILL_HELP_TABLES_GZ_URL})
SAKILA_DB_TAR_GZ=$(shell basename ${SAKILA_DB_TAR_GZ_URL})
SAKILA_SPATIAL_DB_TAR_GZ=$(shell basename ${SAKILA_SPATIAL_DB_TAR_GZ_URL})
WORLD_SQL_GZ=$(shell basename ${WORLD_SQL_GZ_URL})
#MENAGERIE_DB_TAR_GZ=$(shell basename ${MENAGERIE_DB_TAR_GZ_URL})

EMPLOYEES_DB_CODE_TAR=$(shell basename ${EMPLOYEES_DB_CODE_TAR_BZ2} .bz2)
EMPLOYEES_DB_DUMP_FILES_TAR=$(shell basename ${EMPLOYEES_DB_DUMP_FILES_TAR_BZ2} .bz2)
FILL_HELP_TABLES=$(shell basename ${FILL_HELP_TABLES_GZ} .gz)
SAKILA_DB_TAR=$(shell basename ${SAKILA_DB_TAR_GZ} .gz)
SAKILA_SPATIAL_DB_TAR=$(shell basename ${SAKILA_SPATIAL_DB_TAR_GZ} .gz)
WORLD_SQL=$(shell basename ${WORLD_SQL_GZ} .gz)
#MENAGERIE_DB=$(shell basename ${MENAGERIE_DB_TAR_GZ} .gz)

EMPLOYEES_DB_CODE=employees_db/employees.sql
SAKILA_DB=sakila-db/sakila-schema.sql
SAKILA_SPATIAL_DB=sakila-spatial-db/sakila-spatial-schema.sql

EMPLOYEES_DB_DUMP_FILES=employees_db/load_employees.dump
SAKILA_DB_DATA=sakila-db/sakila-data.sql
SAKILA_SPATIAL_DB_DATA=sakila-spatial-db/sakila-spatial-data.sql


EMPLOYEES_DB_CODE_LOADED=${EMPLOYEES_DB_CODE}.loaded
FILL_HELP_TABLES_LOADED=${FILL_HELP_TABLES}.loaded
SAKILA_DB_LOADED=${SAKILA_DB}.loaded
SAKILA_SPATIAL_DB_LOADED=${SAKILA_SPATIAL_DB}.loaded
WORLD_SQL_LOADED=${WORLD_SQL}.loaded

SAKILA_DB_DATA_LOADED=${SAKILA_DB_DATA}.loaded
SAKILA_SPATIAL_DB_DATA_LOADED=${SAKILA_SPATIAL_DB_DATA}.loaded

# DDL only:
SQLS=
SQLS+= ${SAKILA_DB_LOADED}
# .gz file is corrupted:
#SQLS+= ${SAKILA_SPATIAL_DB_LOADED}
SQLS+= ${EMPLOYEES_DB_CODE_LOADED}

# DDL & data:
# not running mysql 5.7.5:
#SQLS+= ${FILL_HELP_TABLES_LOADED}
SQLS+= ${WORLD_SQL_LOADED}

# data only:
SQLS+= ${SAKILA_DB_DATA_LOADED}
# .gz file is corrupted:
#SQLS+= ${SAKILA_SPATIAL_DB_DATA_LOADED}
SQLS+= ${EMPLOYEES_DB_DUMP_FILES_LOADED}

all:	${SQLS}

%.tar: %.tar.gz
	gunzip -c $< >$@

%.tar: %.tar.bz2
	bunzip2 -c $< >$@

%.loaded: %.sql
	${MYSQL} <$<
	touch $@

%.loaded: %.dump
	${MYSQL} <$<
	touch $@

${FILL_HELP_TABLES_GZ}:
	${WGET} -O $@ ${FILL_HELP_TABLES_GZ_URL}
	touch $@

${EMPLOYEES_DB_CODE_TAR_BZ2}:
	${WGET} -O $@ ${EMPLOYEES_DB_CODE_TAR_BZ2_URL}
	touch $@

${EMPLOYEES_DB_DUMP_FILES_TAR_BZ2}:
	${WGET} -O $@ ${EMPLOYEES_DB_DUMP_FILES_TAR_BZ2_URL}
	touch $@

${WORLD_SQL_GZ}:
	${WGET} -O $@ ${WORLD_SQL_GZ_URL}
	touch $@

${SAKILA_DB_TAR_GZ}:
	${WGET} -O $@ ${SAKILA_DB_TAR_GZ_URL}
	touch $@

${SAKILA_SPATIAL_DB_TAR_GZ}:
	${WGET} -O $@ ${SAKILA_SPATIAL_DB_TAR_GZ_URL}
	touch $@



${FILL_HELP_TABLES}: ${FILL_HELP_TABLES_GZ}
	gunzip -c $< >$@

${EMPLOYEES_DB_CODE_TAR}:	${EMPLOYEES_DB_CODE_TAR_BZ2}
	bunzip2 -c $< >$@

${EMPLOYEES_DB_DUMP_FILES_TAR}:	${EMPLOYEES_DB_DUMP_FILES_TAR_BZ2}
	bunzip2 -c $< >$@

${WORLD_SQL}: ${WORLD_SQL_GZ}
	gunzip -c $< >$@

${SAKILA_DB_TAR}:	${SAKILA_DB_TAR_GZ}
	gunzip -c $< >$@

${SAKILA_SPATIAL_DB_TAR}: ${SAKILA_SPATIAL_DB_TAR_GZ}
	gunzip -c $< >$@


${EMPLOYEES_DB_CODE}:	${EMPLOYEES_DB_CODE_TAR}
	tar -xvf $<
	touch $@

${EMPLOYEES_DB_DUMP_FILES}:	${EMPLOYEES_DB_DUMP_FILES_TAR}
	tar -xvf $<
	touch $@

${SAKILA_DB}:	${SAKILA_DB_TAR}
	tar -xvf $<
	touch $@

${SAKILA_SPATIAL_DB}: ${SAKILA_SPATIAL_DB_TAR}
	tar -xvf $<
	touch $@


${FILL_HELP_TABLES_LOADED}:	${FILL_HELP_TABLES}
	${MYSQL} mysql <$<
	touch $@

${SAKILA_DB_LOADED}:	${SAKILA_DB}
	${MYSQL} <$<
	touch $@

${SAKILA_SPATIAL_DB_LOADED}:	${SAKILA_SPATIAL_DB}
	${MYSQL} <$<
	touch $@

${WORLD_SQL_LOADED}: ${WORLD_SQL}
	${MYSQL} -e 'CREATE DATABASE IF NOT EXISTS world;'
	${MYSQL} world <$<
	touch $@



${EMPLOYEES_DB_CODE_LOADED}:	${EMPLOYEES_DB_CODE} ${EMPLOYEES_DB_DUMP_FILES}
	cd employees_db ;\
		${MYSQL} <$(notdir $<)
	touch $@


${SAKILA_DB_DATA_LOADED}: ${SAKILA_DB_DATA} ${SAKILA_DB_LOADED}
	${MYSQL} sakila <$<
	touch $@

${SAKILA_SPATIAL_DB_DATA_LOADED}: ${SAKILA_SPATIAL_DB_DATA} ${SAKILA_SPATIAL_DB_LOADED}
	${MYSQL} sakila <$<
	touch $@


clean:
	-rm -fr *.sql *.tar *.loaded employees_db sakila-db sakila-spatial-db

drop:
	${MYSQL} -e "DROP DATABASE IF EXISTS employee;"
	${MYSQL} -e "DROP DATABASE IF EXISTS sakila;"
	${MYSQL} -e "DROP DATABASE IF EXISTS world;"

debug:
	@echo EMPLOYEES_DB_CODE=${EMPLOYEES_DB_CODE}
	@echo EMPLOYEES_DB_CODE_LOADED=${EMPLOYEES_DB_CODE_LOADED}
	@echo EMPLOYEES_DB_CODE_TAR=${EMPLOYEES_DB_CODE_TAR}
	@echo EMPLOYEES_DB_CODE_TAR_BZ2=${EMPLOYEES_DB_CODE_TAR_BZ2}
	@echo EMPLOYEES_DB_CODE_TAR_BZ2_URL=${EMPLOYEES_DB_CODE_TAR_BZ2_URL}
	@echo EMPLOYEES_DB_DUMP_FILES=${EMPLOYEES_DB_DUMP_FILES}
	@echo EMPLOYEES_DB_DUMP_FILES_TAR=${EMPLOYEES_DB_DUMP_FILES_TAR}
	@echo EMPLOYEES_DB_DUMP_FILES_TAR_BZ2=${EMPLOYEES_DB_DUMP_FILES_TAR_BZ2}
	@echo EMPLOYEES_DB_DUMP_FILES_TAR_BZ2_URL=${EMPLOYEES_DB_DUMP_FILES_TAR_BZ2_URL}
	@echo FILL_HELP_TABLES=${FILL_HELP_TABLES}
	@echo FILL_HELP_TABLES_GZ=${FILL_HELP_TABLES_GZ}
	@echo FILL_HELP_TABLES_GZ_URL=${FILL_HELP_TABLES_GZ_URL}
	@echo FILL_HELP_TABLES_LOADED=${FILL_HELP_TABLES_LOADED}
	@echo MYSQL=${MYSQL}
	@echo SAKILA_DB=${SAKILA_DB}
	@echo SAKILA_DB_DATA=${SAKILA_DB_DATA}
	@echo SAKILA_DB_DATA_LOADED=${SAKILA_DB_DATA_LOADED}
	@echo SAKILA_DB_LOADED=${SAKILA_DB_LOADED}
	@echo SAKILA_DB_TAR=${SAKILA_DB_TAR}
	@echo SAKILA_DB_TAR_GZ=${SAKILA_DB_TAR_GZ}
	@echo SAKILA_DB_TAR_GZ_URL=${SAKILA_DB_TAR_GZ_URL}
	@echo SAKILA_SPATIAL_DB=${SAKILA_SPATIAL_DB}
	@echo SAKILA_SPATIAL_DB_DATA=${SAKILA_SPATIAL_DB_DATA}
	@echo SAKILA_SPATIAL_DB_DATA_LOADED=${SAKILA_SPATIAL_DB_DATA_LOADED}
	@echo SAKILA_SPATIAL_DB_LOADED=${SAKILA_SPATIAL_DB_LOADED}
	@echo SAKILA_SPATIAL_DB_TAR=${SAKILA_SPATIAL_DB_TAR}
	@echo SAKILA_SPATIAL_DB_TAR_GZ=${SAKILA_SPATIAL_DB_TAR_GZ}
	@echo SAKILA_SPATIAL_DB_TAR_GZ_URL=${SAKILA_SPATIAL_DB_TAR_GZ_URL}
	@echo SQLS=${SQLS}
	@echo WGET=${WGET}
	@echo WORLD_SQL=${WORLD_SQL}
	@echo WORLD_SQL_GZ=${WORLD_SQL_GZ}
	@echo WORLD_SQL_GZ_URL=${WORLD_SQL_GZ_URL}
	@echo WORLD_SQL_LOADED=${WORLD_SQL_LOADED}
