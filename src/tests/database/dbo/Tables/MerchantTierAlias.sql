CREATE TABLE [dbo].[MerchantTierAlias] (
    [MerchantTierAliasId] INT            IDENTITY (1, 1) NOT NULL,
    [MerchantId]          INT            NOT NULL,
    [TierReference]       NVARCHAR (50)  NOT NULL,
    [TierAliases]         NVARCHAR (MAX) NULL,
    [Status]              INT            CONSTRAINT [DF_MerchantTierAlias_Status] DEFAULT ((1)) NOT NULL,
    CONSTRAINT [PK_MerchantTierAlias] PRIMARY KEY CLUSTERED ([MerchantTierAliasId] ASC),
    CONSTRAINT [FK_MerchantTierAlias_Merchant] FOREIGN KEY ([MerchantId]) REFERENCES [dbo].[Merchant] ([MerchantId])
);

