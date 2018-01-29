CREATE TABLE [dbo].[MerchantTier] (
    [MerchantTierId]      INT             IDENTITY (1, 1) NOT NULL,
    [MerchantId]          INT             NOT NULL,
    [TierName]            NVARCHAR (500)  NOT NULL,
    [TierDescription]     NVARCHAR (MAX)  NULL,
    [StartDate]           DATETIME        NOT NULL,
    [EndDate]             DATETIME        NOT NULL,
    [CurrencyId]          INT             NULL,
    [Commission]          DECIMAL (18, 2) NOT NULL,
    [TierTypeId]          INT             NOT NULL,
    [TierCommTypeId]      INT             NOT NULL,
    [Status]              INT             NOT NULL,
    [TrackingLink]        NVARCHAR (500)  NOT NULL,
    [TierReference]       NVARCHAR (500)  NULL,
    [TierDescriptionLong] NVARCHAR (MAX)  NULL,
    [TierAlias]           NVARCHAR (2000) NULL,
    [IsAdvancedTier]      BIT             CONSTRAINT [DF_MerchantTier_IsAdvancedTier] DEFAULT ((0)) NOT NULL,
    [MerchantTierAliasId] INT             NULL,
    [TierImageUrl] NVARCHAR(MAX) NULL, 
    [TierSpecialTerms] NVARCHAR(MAX) NULL, 
    [TierExclusions] NVARCHAR(MAX) NULL, 
    CONSTRAINT [PK_MerchantTier] PRIMARY KEY CLUSTERED ([MerchantTierId] ASC),
    CONSTRAINT [FK_MerchantTier_Currency] FOREIGN KEY ([CurrencyId]) REFERENCES [dbo].[Currency] ([CurrencyId]),
    CONSTRAINT [FK_MerchantTier_Merchant] FOREIGN KEY ([MerchantId]) REFERENCES [dbo].[Merchant] ([MerchantId]),
    CONSTRAINT [FK_MerchantTier_MerchantTier] FOREIGN KEY ([MerchantTierId]) REFERENCES [dbo].[MerchantTier] ([MerchantTierId]),
    CONSTRAINT [FK_MerchantTier_MerchantTierAlias] FOREIGN KEY ([MerchantTierAliasId]) REFERENCES [dbo].[MerchantTierAlias] ([MerchantTierAliasId]),
    CONSTRAINT [FK_MerchantTier_MerchantTierCommType] FOREIGN KEY ([TierCommTypeId]) REFERENCES [dbo].[MerchantTierCommType] ([TierCommTypeId]),
    CONSTRAINT [FK_MerchantTier_MerchantTierType] FOREIGN KEY ([TierTypeId]) REFERENCES [dbo].[MerchantTierType] ([TierTypeId])
);


GO
CREATE NONCLUSTERED INDEX [IX_MerchantTier_MerchantId]
    ON [dbo].[MerchantTier]([MerchantId] ASC);


GO
CREATE NONCLUSTERED INDEX [X-NonClusteredIndex-20150909-215003]
    ON [dbo].[MerchantTier]([StartDate] ASC, [EndDate] ASC, [Status] ASC)
    INCLUDE([TierName], [TierDescription], [CurrencyId], [Commission], [TierTypeId], [TierCommTypeId], [TrackingLink], [TierDescriptionLong]);

