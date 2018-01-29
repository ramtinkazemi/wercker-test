#!/bin/bash

set -e
#wait for the SQL Server to come up
#sleep 60s
shopt -s expand_aliases
export SA_PASSWORD='<YourStrong!Passw0rd>'
alias runsql="/opt/mssql-tools/bin/sqlcmd -S mssql -U sa -P '$SA_PASSWORD'"

while ! runsql -Q 'select getdate()'; do sleep 3; done

DIR="$( cd "$( dirname "${BASH_SOURCE[0]}" )" && pwd )"

runsql -i $DIR/database/up.sql
source $DIR/import-schema.sh