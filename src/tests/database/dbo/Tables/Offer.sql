CREATE TABLE [dbo].[Offer] (
    [OfferId]              INT            IDENTITY (1, 1) NOT NULL,
    [NetworkId]            INT            NOT NULL,
    [MerchantId]           INT            NOT NULL,
    [Status]               INT            NOT NULL,
    [IsFeatured]           BIT            CONSTRAINT [DF_Offer_IsFeatured] DEFAULT ((0)) NOT NULL,
    [OfferReference]       NVARCHAR (500) NULL,
    [CouponCode]           NVARCHAR (100) NULL,
    [OfferTitle]           NVARCHAR (500) NOT NULL,
    [OfferDescription]     NVARCHAR (MAX) NULL,
    [TrackingLink]         NVARCHAR (500) NULL,
    [DateStart]            DATETIME       NOT NULL,
    [DateEnd]              DATETIME       NOT NULL,
    [OfferTerms]           NVARCHAR (MAX) NULL,
    [DateApiUpdated]       DATETIME       CONSTRAINT [DF_Offer_DateApiUpdated] DEFAULT (getdate()) NOT NULL,
    [IsManualEntry]        BIT            CONSTRAINT [DF_Offer_IsManualEntry] DEFAULT ((0)) NOT NULL,
    [IsDateStartAltered]   BIT            CONSTRAINT [DF_Offer_IsDateStartAltered] DEFAULT ((0)) NOT NULL,
    [IsDateEndAltered]     BIT            CONSTRAINT [DF_Offer_IsDateEndAltered] DEFAULT ((0)) NOT NULL,
    [IsRecentlyDownloaded] BIT            CONSTRAINT [DF_Offer_IsRecentlyDownloaded] DEFAULT ((0)) NOT NULL,
    [HyphenatedString]     NVARCHAR (450) CONSTRAINT [DF_Offer_HyphenatedString] DEFAULT (N'Not Available') NULL,
    [Comment]              NVARCHAR (MAX) NULL,
    [Ranking]              INT  DEFAULT ((0)) NOT NULL,
    CONSTRAINT [OfferId] PRIMARY KEY CLUSTERED ([OfferId] ASC),
    CONSTRAINT [FK_Offer_Network] FOREIGN KEY ([NetworkId]) REFERENCES [dbo].[Network] ([NetworkId])
);


GO
CREATE NONCLUSTERED INDEX [IX_Offer]
    ON [dbo].[Offer]([HyphenatedString] ASC);


GO
CREATE NONCLUSTERED INDEX [IX_Offer_IsFeatured]
    ON [dbo].[Offer]([IsFeatured] ASC);


GO
CREATE NONCLUSTERED INDEX [IX_Offer_Status]
    ON [dbo].[Offer]([Status] ASC)
    INCLUDE([OfferId]);


GO
CREATE NONCLUSTERED INDEX [X-NonClusteredIndex-20150909-214154]
    ON [dbo].[Offer]([Status] ASC, [DateStart] ASC, [DateEnd] ASC)
    INCLUDE([CouponCode], [OfferTitle], [OfferDescription], [TrackingLink], [OfferTerms], [OfferId]);


GO
CREATE NONCLUSTERED INDEX [NonClusteredIndex-20160221-115901]
    ON [dbo].[Offer]([MerchantId] ASC, [Status] ASC, [DateStart] ASC, [DateEnd] ASC)
    INCLUDE([OfferId], [IsFeatured], [CouponCode], [OfferTitle], [OfferDescription], [TrackingLink], [OfferTerms], [HyphenatedString]);


GO
-- =============================================
-- Author:		Ramanathan Rajendran
-- Create date: 25/09/2013
-- Description:	Tigger to update the hypanated strincg column on insert and update
-- =============================================
CREATE TRIGGER [dbo].[tgr_UpdateOfferHypenatedString]
   ON  [dbo].[Offer]
   AFTER INSERT,UPDATE
AS 
BEGIN
	SET NOCOUNT ON;

	--DECLARE @OfferId INT

	--SELECT @OfferId = OfferId From INSERTED
	
	--UPDATE dbo.Offer SET HyphenatedString = dbo.fn_GetHyphenatedString(OfferTitle,OfferId) WHERE OfferId = @OfferId

	UPDATE dbo.Offer SET HyphenatedString=dbo.fn_GetHyphenatedString(i.OfferTitle, i.OfferId) From dbo.Offer o 
	INNER JOIN inserted i ON o.OfferId = i.OfferId

END

GO

CREATE TRIGGER [dbo].[tgr_InsertOfferClientMapping]
   ON  [dbo].[Offer]
   AFTER INSERT
AS 
BEGIN
	SET NOCOUNT ON;
	
	--IF(SELECT IsManualEntry FROM inserted) = 1

	--BEGIN

	--	DECLARE @clientId INT
	--	DECLARE @ClientCursor CURSOR
	
	--	SET @ClientCursor = CURSOR FAST_FORWARD FOR SELECT ClientId FROM Client WHERE [Status] = 1
	
	--	OPEN @ClientCursor
	--	FETCH NEXT FROM @ClientCursor INTO @clientId
	
	--	WHILE @@FETCH_STATUS = 0
	--		BEGIN
		
	--		INSERT INTO [dbo].[OfferClientMap]([ClientId],[OfferId])
	--		SELECT @clientId, OfferId FROM inserted
		 
	--		FETCH NEXT FROM @ClientCursor INTO @clientId
	--		END
		
	--	CLOSE @ClientCursor
	--	DEALLOCATE @ClientCursor

	--END
END
