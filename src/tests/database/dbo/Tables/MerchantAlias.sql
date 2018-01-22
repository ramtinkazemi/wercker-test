CREATE TABLE [dbo].[MerchantAlias] (
    [MerchantAliasId]  INT            IDENTITY (1000000, 1) NOT NULL,
    [MerchantId]       INT            NOT NULL,
    [MerchantName]     NVARCHAR (400) NOT NULL,
    [WebsiteUrl]       NVARCHAR (500) NULL,
    [DescriptionLong]  NVARCHAR (MAX) NULL,
    [DescriptionShort] NVARCHAR (MAX) NULL,
    [DeepLink]         NVARCHAR (500) NULL,
    [BasicTerms]       NVARCHAR (MAX) NULL,
    [HyphenatedString] NVARCHAR (450) NOT NULL,
    [Status]           INT            NULL,
    [IsFeatured]       BIT            NULL,
    [TopFeatured]      BIT            CONSTRAINT [DF_MerchantAlias_TopFeatured] DEFAULT ((0)) NULL,
    CONSTRAINT [PK_MerchantStore1] PRIMARY KEY CLUSTERED ([MerchantAliasId] ASC),
    CONSTRAINT [FK_MerchantStore_Merchant1] FOREIGN KEY ([MerchantId]) REFERENCES [dbo].[Merchant] ([MerchantId])
);


GO

CREATE TRIGGER [dbo].[tgr_UpdateMerchantAliasHypenatedString]
   ON  [dbo].[MerchantAlias]
   AFTER INSERT,UPDATE
AS
BEGIN
       SET NOCOUNT ON;
      
       UPDATE dbo.MerchantAlias SET HyphenatedString = dbo.fn_GetHyphenatedString(MerchantName,NULL)
 
END