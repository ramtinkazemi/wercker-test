#!/bin/bash

set -e
#wait for the SQL Server to come up
shopt -s expand_aliases
#run the setup script to create the DB and the schema in the DB
export SA_PASSWORD='<YourStrong!Passw0rd>'
alias runsql="/opt/mssql-tools/bin/sqlcmd -S mssql -U sa -P '$SA_PASSWORD' -b "
export DATABASE=ShopGo_Development

DIR="$( cd "$( dirname "${BASH_SOURCE[0]}" )" && pwd )"

runsql -d $DATABASE -i $DIR/database/Security/PIIColumnMasterKey.sql
runsql -d $DATABASE -i $DIR/database/Security/PIIColumnEncryptionKey.sql
runsql -d $DATABASE -i $DIR/database/dbo/Tables/ClientAccessType.sql
runsql -d $DATABASE -i $DIR/database/dbo/Tables/ClientLayoutTheme.sql
runsql -d $DATABASE -i $DIR/database/dbo/Tables/ClientProgramType.sql
runsql -d $DATABASE -i $DIR/database/dbo/Tables/ClientRewardType.sql
runsql -d $DATABASE -i $DIR/database/dbo/Tables/ClientType.sql
runsql -d $DATABASE -i $DIR/database/dbo/Tables/Client.sql

runsql -d $DATABASE -i $DIR/database/dbo/Stored\ Procedures/Sp_PublishChanges.sql

runsql -d $DATABASE -i $DIR/database/dbo/Tables/Member.sql
runsql -d $DATABASE -i $DIR/database/dbo/Tables/MemberClicks.sql

runsql -d $DATABASE -i $DIR/database/dbo/Tables/Country.sql
runsql -d $DATABASE -i $DIR/database/dbo/Tables/TimeZone.sql
runsql -d $DATABASE -i $DIR/database/dbo/Tables/Network.sql
runsql -d $DATABASE -i $DIR/database/dbo/Tables/Merchant.sql
runsql -d $DATABASE -i $DIR/database/dbo/Tables/MerchantAlias.sql

runsql -d $DATABASE -i $DIR/database/dbo/Tables/Currency.sql
runsql -d $DATABASE -i $DIR/database/dbo/Tables/MerchantTierAlias.sql
runsql -d $DATABASE -i $DIR/database/dbo/Tables/MerchantTierCommType.sql
runsql -d $DATABASE -i $DIR/database/dbo/Tables/MerchantTierType.sql
runsql -d $DATABASE -i $DIR/database/dbo/Tables/MerchantTier.sql
runsql -d $DATABASE -i $DIR/database/dbo/Tables/MerchantClientMap.sql
runsql -d $DATABASE -i $DIR/database/dbo/Tables/MerchantTierClient.sql
runsql -d $DATABASE -i $DIR/database/dbo/Views/MerchantTierView.sql
runsql -d $DATABASE -i $DIR/database/dbo/Views/ConsolidatedMerchantTierView.sql
runsql -d $DATABASE -i $DIR/database/dbo/Tables/Offer.sql
runsql -d $DATABASE -i $DIR/database/dbo/Views/MerchantOfferCountView.sql
runsql -d $DATABASE -i $DIR/database/dbo/Views/MerchantView.sql

runsql -d $DATABASE -i $DIR/database/dbo/Tables/GstStatus.sql
runsql -d $DATABASE -i $DIR/database/dbo/Tables/TransactionStatus.sql
runsql -d $DATABASE -i $DIR/database/dbo/Tables/TransactionType.sql
runsql -d $DATABASE -i $DIR/database/dbo/Tables/Transaction.sql

runsql -d $DATABASE -i $DIR/database/dbo/Tables/Campaign.sql

runsql -d $DATABASE -i $DIR/database/dbo/Tables/ReportSubscription.sql

#import the data from the csv file
#/opt/mssql-tools/bin/bcp DemoData.dbo.Products in "/usr/src/app/Products.csv" -c -t',' -S localhost -U sa -P $SA_PASSWORD
