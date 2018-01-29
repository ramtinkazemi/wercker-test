CREATE TABLE [dbo].[MerchantTierType] (
    [TierTypeId]   INT            IDENTITY (100, 1) NOT NULL,
    [TierType]     VARCHAR (100)  NOT NULL,
    [TierCssClass] NVARCHAR (100) NULL,
    [IsExtra]      BIT            NULL,
    CONSTRAINT [PK_MerchantTierType] PRIMARY KEY CLUSTERED ([TierTypeId] ASC)
);

