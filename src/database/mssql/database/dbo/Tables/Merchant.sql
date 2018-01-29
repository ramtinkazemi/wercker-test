CREATE TABLE [dbo].[Merchant] (
    [MerchantId]          INT             IDENTITY (1000000, 1) NOT NULL,
    [NetworkId]           INT             NOT NULL,
    [CurrencyId]          INT             NULL,
    [Status]              INT             NOT NULL,
    [ReferenceName]       NVARCHAR (500)  NULL,
    [ReferenceId]         NVARCHAR (500)  NULL,
    [MerchantName]        NVARCHAR (400)  NOT NULL,
    [WebsiteUrl]          NVARCHAR (500)  NULL,
    [DescriptionShort]    NVARCHAR (2000) NULL,
    [DescriptionLong]     NVARCHAR (MAX)  NULL,
    [BasicTerms]          NVARCHAR (MAX)  NULL,
    [ExtentedTerms]       NVARCHAR (MAX)  NULL,
    [InternationalTerms]  NVARCHAR (MAX)  NULL,
    [MobileEnabled]       BIT             NULL,
    [TabletEnabled]       BIT             NULL,
    [IsFeatured]          BIT             NULL,
    [IsHomePageFeatured]  BIT             NULL,
    [CountryId]           INT             NOT NULL,
    [TimeZoneId]          INT             NOT NULL,
    [DownloadProduct]     BIT             CONSTRAINT [DF_Merchant_DownloadProduct] DEFAULT ((1)) NOT NULL,
    [DowloadOffer]        BIT             CONSTRAINT [DF_Merchant_DowloadOffer] DEFAULT ((1)) NOT NULL,
    [DownloadTransaction] BIT             CONSTRAINT [DF_Merchant_DownloadTransaction] DEFAULT ((0)) NULL,
    [DownloadProductUrl]  NVARCHAR (500)  NULL,
    [HyphenatedString]    NVARCHAR (450)  CONSTRAINT [DF_Merchant_HyphenatedString] DEFAULT (N'Not Available') NOT NULL,
    [IsPopular]           BIT             CONSTRAINT [DF_Merchant_IsPopular] DEFAULT ((0)) NULL,
    [TrackingTime]        NVARCHAR (200)  NULL,
    [ApprovalTime]        NVARCHAR (200)  NULL,
    [IsLatest]            BIT             NULL,
    [Keywords]            NVARCHAR (4000) NULL,
    [Comment]             NVARCHAR (MAX)  NULL,
    [ApprovalWaitDays]    INT             CONSTRAINT [DF_Merchant_ApprovalWaitDays] DEFAULT ((90)) NOT NULL,
    [ClickWindowCheck]    BIT             CONSTRAINT [DF_Merchant_ClickWindowCheck] DEFAULT ((0)) NOT NULL,
    [NotificationMsg]     NVARCHAR (500)  NULL,
    [ConfirmationMsg]     NVARCHAR (500)  NULL,
    [WaitTimeCheck]       BIT             CONSTRAINT [DF_Merchant_WaitTimeCheck] DEFAULT ((1)) NOT NULL,
    [IsConsumption]       BIT             CONSTRAINT [DF_Merchant_IsConsumption] DEFAULT ((0)) NULL,
    [IsToolbarEnabled]    BIT             CONSTRAINT [DF_Merchant_IsToolbarEnabled] DEFAULT ((1)) NULL,
    [IsLuxuryBrand] BIT NOT NULL DEFAULT ((0)), 
    [IsToolbarOptoutSerps] BIT NOT NULL DEFAULT ((1)), 
    [IsToolbarOptoutSliderActivation] BIT NOT NULL DEFAULT ((1)),
    [IsToolbarOptoutSearch] BIT NOT NULL DEFAULT ((1)),
    CONSTRAINT [PK_Merchant] PRIMARY KEY CLUSTERED ([MerchantId] ASC),
    CONSTRAINT [FK_Merchant_Country] FOREIGN KEY ([CountryId]) REFERENCES [dbo].[Country] ([CountryId]),
    CONSTRAINT [FK_Merchant_Network] FOREIGN KEY ([NetworkId]) REFERENCES [dbo].[Network] ([NetworkId]),
    CONSTRAINT [FK_Merchant_TimeZone] FOREIGN KEY ([TimeZoneId]) REFERENCES [dbo].[TimeZone] ([TimeZoneId])
);


GO
CREATE NONCLUSTERED INDEX [IX_Merchant]
    ON [dbo].[Merchant]([HyphenatedString] ASC);


GO
CREATE NONCLUSTERED INDEX [IX_Merchant_IsHomePageFeatured]
    ON [dbo].[Merchant]([IsHomePageFeatured] ASC);


GO
CREATE NONCLUSTERED INDEX [IX_Merchant_IsFeatured]
    ON [dbo].[Merchant]([IsFeatured] ASC);


GO
CREATE NONCLUSTERED INDEX [IX_Merchant_IsLatest]
    ON [dbo].[Merchant]([IsLatest] ASC);


GO
CREATE NONCLUSTERED INDEX [IX_Merchant_IsPopular]
    ON [dbo].[Merchant]([IsPopular] ASC);


GO
CREATE NONCLUSTERED INDEX [IX_Merchant_Status]
    ON [dbo].[Merchant]([Status] ASC);


GO
CREATE TRIGGER [dbo].[tgr_InsertMerchantDictionary]
   ON  dbo.Merchant
   AFTER INSERT
AS 
BEGIN
	SET NOCOUNT ON;

	
INSERT INTO [dbo].[DictionaryMerchant]
           ([MerchantId]
           ,[MerchantName]
           ,[Keyword])

		   SELECT MerchantId, MerchantName, keywords
		   FROM Inserted
END

GO
-- =============================================
-- Author:		Ramanathan Rajendran
-- Create date: 25/09/2013
-- Description:	Tigger to update the hypanated strincg column on insert and update
-- =============================================
CREATE TRIGGER [dbo].[tgr_UpdateMerchantHypenatedString]
   ON  dbo.Merchant
   AFTER INSERT,UPDATE
AS 
BEGIN
	SET NOCOUNT ON;
	
	UPDATE dbo.Merchant SET HyphenatedString = dbo.fn_GetHyphenatedString(MerchantName,NULL)

END

GO
CREATE TRIGGER [dbo].[tgr_UpdateMerchantDictionary]
   ON  dbo.Merchant
   AFTER UPDATE
AS 
BEGIN
	SET NOCOUNT ON;

	DECLARE @MerchantName NVARCHAR(MAX)
	DECLARE @Keyword NVARCHAR(MAX)
	DECLARE @MerchantId INT
	DECLARE @Status INT

	IF ( UPDATE(MerchantName) OR UPDATE(Keywords) OR UPDATE([Status]))
	BEGIN

		SELECT @MerchantId = MerchantId, @MerchantName = MerchantName, @Keyword = keywords, @Status = [status] From INSERTED

		IF(@Status = 1)
			BEGIN
				IF(EXISTS(SELECT * FROM DictionaryMerchant WHERE MerchantId = @MerchantId))
					UPDATE [DictionaryMerchant] SET MerchantName = @MerchantName, Keyword = @Keyword WHERE MerchantId = @MerchantId
				ELSE
					INSERT INTO [dbo].[DictionaryMerchant] ([MerchantId],[MerchantName],[Keyword]) VALUES (@MerchantId,@MerchantName,@Keyword)
			END
		ELSE
			BEGIN
				DELETE FROM DictionaryMerchant WHERE MerchantId = @MerchantId
			END
	END
END
