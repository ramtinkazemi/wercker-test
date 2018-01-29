CREATE TABLE [dbo].[MerchantTierCommType] (
    [TierCommTypeId] INT           IDENTITY (100, 1) NOT NULL,
    [TierCommType]   VARCHAR (100) NOT NULL,
    CONSTRAINT [PK_MerchantTierCommType] PRIMARY KEY CLUSTERED ([TierCommTypeId] ASC)
);

