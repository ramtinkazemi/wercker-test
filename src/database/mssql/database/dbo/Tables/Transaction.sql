CREATE TABLE [dbo].[Transaction] (
    [TransactionId]        INT             IDENTITY (1, 1) NOT NULL,
    [TransactionDisplayId] NVARCHAR (50)   NOT NULL,
    [TransactionReference] NVARCHAR (50)   NULL,
    [NetworkId]            INT             NOT NULL,
    [MerchantId]           INT             NOT NULL,
    [ClientId]             INT             NOT NULL,
    [MemberId]             INT             NOT NULL,
    [TransactionStatusId]  INT             NOT NULL,
    [GstStatusId]          INT             NULL,
    [TransCurrencyId]      INT             NOT NULL,
    [SaleDate]             DATETIME        NOT NULL,
    [SaleDateAest]         DATETIME        NULL,
    [TransExchangeRate]    MONEY           CONSTRAINT [DF_Transaction_SaleCurrExRate] DEFAULT ((1)) NOT NULL,
    [PromotionValueAud]    MONEY           CONSTRAINT [DF_Table_1_PromotionValueAUD] DEFAULT ((0)) NOT NULL,
    [ApprovalMailSent]     BIT             CONSTRAINT [DF_Table_1_ApprovedMailSent] DEFAULT ((0)) NOT NULL,
    [PendingMailSent]      BIT             CONSTRAINT [DF_Transaction_PendingMailSent] DEFAULT ((0)) NOT NULL,
    [IsManualEntry]        BIT             CONSTRAINT [DF_Transaction_IsManualEntry] DEFAULT ((0)) NOT NULL,
    [Status]               INT             CONSTRAINT [DF_Table_1_IsDeleted] DEFAULT ((0)) NOT NULL,
    [NetworkTranStatusId]  INT             CONSTRAINT [DF_Transaction_NetworkTranStatus] DEFAULT ((100)) NULL,
    [Comment]              NVARCHAR (1000) NULL,
    [NetworkTracked]       BIT             CONSTRAINT [DF_Transaction_NetworkTracked] DEFAULT ((1)) NULL,
    [DateCreated]          DATETIME        CONSTRAINT [DF_Transaction_DateCreated] DEFAULT (getdate()) NULL,
    [LastUpdated]          DATETIME        NULL,
    [PolicyId]             INT             NULL,
    [DateApproved]         DATETIME        NULL,
    [IsLocked]             BIT             CONSTRAINT [DF_Transaction_IsLocked] DEFAULT ((0)) NOT NULL,
    [IsMasterLocked]       BIT             CONSTRAINT [DF_Transaction_IsMasterLocked] DEFAULT ((0)) NOT NULL,
    [Md5Hash]              NVARCHAR (50)   NULL,
    [OrderId]              NVARCHAR (100)  NULL,
    [TransactionTypeId] INT NULL, 
    CONSTRAINT [PK_Transaction] PRIMARY KEY CLUSTERED ([TransactionId] ASC),
    CONSTRAINT [FK_Transaction_Client] FOREIGN KEY ([ClientId]) REFERENCES [dbo].[Client] ([ClientId]),
    CONSTRAINT [FK_Transaction_Currency] FOREIGN KEY ([TransCurrencyId]) REFERENCES [dbo].[Currency] ([CurrencyId]),
    CONSTRAINT [FK_Transaction_GstStatus] FOREIGN KEY ([GstStatusId]) REFERENCES [dbo].[GstStatus] ([GstStatusId]),
    CONSTRAINT [FK_Transaction_Member] FOREIGN KEY ([MemberId]) REFERENCES [dbo].[Member] ([MemberId]),
    CONSTRAINT [FK_Transaction_Network] FOREIGN KEY ([NetworkId]) REFERENCES [dbo].[Network] ([NetworkId]),
    CONSTRAINT [FK_Transaction_Transaction] FOREIGN KEY ([TransactionId]) REFERENCES [dbo].[Transaction] ([TransactionId]),
    CONSTRAINT [FK_Transaction_TransactionStatus] FOREIGN KEY ([TransactionStatusId]) REFERENCES [dbo].[TransactionStatus] ([TransactionStatusId]),
    CONSTRAINT [FK_Transaction_TransactionStatus1] FOREIGN KEY ([NetworkTranStatusId]) REFERENCES [dbo].[TransactionStatus] ([TransactionStatusId]), 
    CONSTRAINT [FK_Transaction_TransactionType] FOREIGN KEY ([TransactionTypeId]) REFERENCES [TransactionType]([TransactionTypeId])
);


GO
CREATE NONCLUSTERED INDEX [IX_Transaction_MemberId]
    ON [dbo].[Transaction]([MemberId] ASC);


GO
CREATE NONCLUSTERED INDEX [NonClusteredIndex-20160220-121050]
    ON [dbo].[Transaction]([IsManualEntry] ASC, [IsLocked] ASC)
    INCLUDE([TransactionId], [TransactionReference], [MerchantId], [MemberId], [SaleDate], [NetworkTracked]);


GO
CREATE NONCLUSTERED INDEX [NonClusteredIndex-20160228-170339]
    ON [dbo].[Transaction]([NetworkId] ASC, [SaleDate] ASC, [Status] ASC)
    INCLUDE([TransactionId], [TransactionReference], [MerchantId], [TransactionStatusId], [TransExchangeRate], [NetworkTranStatusId], [MemberId], [DateCreated], [Comment]);


GO

CREATE NONCLUSTERED INDEX [NonClusteredIndex-TransactionReference] ON [dbo].[Transaction]
(
	[TransactionReference] ASC
)WITH (PAD_INDEX = OFF, STATISTICS_NORECOMPUTE = OFF, SORT_IN_TEMPDB = OFF, DROP_EXISTING = OFF, ONLINE = OFF, ALLOW_ROW_LOCKS = ON, ALLOW_PAGE_LOCKS = ON) ON [PRIMARY]
GO


CREATE NONCLUSTERED INDEX [NonClusteredIndex-OrderId] ON [dbo].[Transaction]
(
	[OrderId] ASC
)WITH (PAD_INDEX = OFF, STATISTICS_NORECOMPUTE = OFF, SORT_IN_TEMPDB = OFF, DROP_EXISTING = OFF, ONLINE = OFF, ALLOW_ROW_LOCKS = ON, ALLOW_PAGE_LOCKS = ON) ON [PRIMARY]
GO

CREATE NONCLUSTERED INDEX [NonClusteredIndex-MerchantId] ON [dbo].[Transaction]
(
	[MerchantId] ASC
)WITH (PAD_INDEX = OFF, STATISTICS_NORECOMPUTE = OFF, SORT_IN_TEMPDB = OFF, DROP_EXISTING = OFF, ONLINE = OFF, ALLOW_ROW_LOCKS = ON, ALLOW_PAGE_LOCKS = ON) ON [PRIMARY]
GO
CREATE NONCLUSTERED INDEX [IDX_TransactionStatusId] ON [dbo].[Transaction]
(
	[TransactionStatusId] ASC,
	[Status] ASC,
	[TransactionReference] ASC,
	[IsLocked] ASC,
	[IsMasterLocked] ASC
)
INCLUDE (
    [NetworkId],
	[MerchantId],
	[ClientId],
	[SaleDate],
	[Comment])
GO

CREATE TRIGGER
    [dbo].[tgr_InsertTransactionWoopra]
ON 
    [dbo].[Transaction]
FOR INSERT 
AS
  BEGIN
    SET NOCOUNT ON 

	DECLARE @StartRow int
    DECLARE @EndRow int
    DECLARE @CurrentRow int

    SET @StartRow = 1
    SET @EndRow = (SELECT count(*) FROM inserted)
    SET @CurrentRow = @StartRow

	--WHEN INSERT
	BEGIN
			INSERT INTO [dbo].[WoopraData](WoopraEntityTypeId,EntityId
								--,EntityType
				,WoopraEntityActionId,DateAdded,DatePushed)
							 SELECT 1,TransactionId
							 --,'Member'
							 ,1,GETDATE(),NULL 
				FROM inserted

            --[DEV-1448] Quick workaround to populate transaction type id
            UPDATE t SET t.[TransactionTypeId] = 1 
            FROM INSERTED i 
            INNER JOIN [dbo].[Transaction]  t on i.TransactionId = t.TransactionId 
            INNER JOIN [dbo].[FriendBuyConversion] f ON t.MemberId = f.MateMemberId
            WHERE t.[TransactionTypeId] IS NULL  

            UPDATE t SET t.[TransactionTypeId] = 2 
            FROM INSERTED i 
            INNER JOIN [dbo].[Transaction]  t on i.TransactionId = t.TransactionId 
            INNER JOIN [dbo].[FriendBuyConversion] f ON t.MemberId = f.ReferralMemberId
            WHERE t.[TransactionTypeId] IS NULL 

            UPDATE t SET t.[TransactionTypeId] = 6 
            FROM INSERTED i 
            INNER JOIN [dbo].[Transaction]  t on i.TransactionId = t.TransactionId 
            WHERE t.TransactionTypeId IS NULL AND t.IsManualEntry = 1 AND t.NetworkTracked = 0

            UPDATE t SET t.TransactionTypeId = CASE WHEN t.TransactionReference LIKE '%REACTIVATION%' THEN 3 ELSE 4 END
            FROM network n 
            INNER JOIN Merchant m on m.NetworkId = n.NetworkId
            INNER JOIN dbo.[Transaction] t ON t.MerchantId = m.MerchantId
            INNER JOIN INSERTED i on i.TransactionId = t.TransactionId 
            WHERE t.[TransactionTypeId] IS NULL AND n.NetworkName = 'shopgo';

            UPDATE t SET t.[TransactionTypeId] = 5 
            FROM INSERTED i 
            INNER JOIN [dbo].[Transaction]  t on i.TransactionId = t.TransactionId 
            WHERE t.TransactionTypeId IS NULL;
	END
	
END --END TRIGGER







GO


CREATE TRIGGER
    [dbo].[tgr_UpdateTransactionWoopra]
ON 
    [dbo].[Transaction]
FOR UPDATE 
AS
  BEGIN
    SET NOCOUNT ON 

	DECLARE @StartRow int
    DECLARE @EndRow int
    DECLARE @CurrentRow int

    SET @StartRow = 1
    SET @EndRow = (SELECT count(*) FROM inserted)
    SET @CurrentRow = @StartRow

	--WHEN INSERT
	
	BEGIN
	IF EXISTS(SELECT  TransactionId,MemberId,MerchantId,TransCurrencyId,TransactionStatusId FROM inserted
          EXCEPT
          SELECT TransactionId,MemberId,MerchantId,TransCurrencyId,TransactionStatusId FROM deleted )   
		   -- Tests for modifications to fields that we are interested in
	BEGIN
			  -- Put code here that does the work in the trigger
		INSERT INTO [dbo].[WoopraData](WoopraEntityTypeId,EntityId
								--,EntityType
				,WoopraEntityActionId,DateAdded,DatePushed)
							 SELECT 1,TransactionId
							 --,'Member'
							 ,2,GETDATE(),NULL 
				FROM inserted
	END
	END
	
END --END TRIGGER



GO

CREATE TRIGGER [dbo].[tgr_UpdateTransactionStatus]
   ON  [dbo].[Transaction]
   AFTER UPDATE
AS 
BEGIN
	SET NOCOUNT ON;
	DECLARE @newStatus INT
	DECLARE @oldStatus INT
	DECLARE @transactionId INT
	
	DECLARE  updateCursor CURSOR FOR 
	SELECT TransactionId, TransactionStatusId FROM inserted

	OPEN updateCursor
	FETCH NEXT FROM updateCursor INTO @transactionId, @newStatus
	WHILE @@FETCH_STATUS = 0
	BEGIN
		SELECT @oldStatus = (SELECT TransactionStatusId FROM deleted WHERE TransactionId=@transactionId)
		IF (@newStatus = 101 AND @oldStatus = 100)
		BEGIN
			UPDATE [Transaction] SET DateApproved = GETDATE() WHERE TransactionId=@transactionId
			INSERT INTO EntityAudit (EntityType, EntityId,UserId, Comment, DateCreated, FieldsAffected) 
			values ('Transaction', @transactionId, 0, 'Status updated to Approved', GETDATE(),'TransactionStatusId')
		END
		ELSE IF (@newStatus = 102 AND @oldStatus = 100)
		BEGIN
			UPDATE [Transaction] SET DateApproved = GETDATE() WHERE TransactionId=@transactionId
			INSERT INTO EntityAudit (EntityType, EntityId,UserId, Comment, DateCreated, FieldsAffected) 
			values ('Transaction', @transactionId, 0, 'Status updated to Declined', GETDATE(),'TransactionStatusId')
		END
		FETCH NEXT FROM updateCursor INTO @transactionId, @newStatus
	END
	CLOSE updateCursor

END




GO
EXEC sp_addextendedproperty @name = N'MS_Description',
    @value = N'Transaction type id',
    @level0type = N'SCHEMA',
    @level0name = N'dbo',
    @level1type = N'TABLE',
    @level1name = N'Transaction',
    @level2type = N'COLUMN',
    @level2name = N'TransactionTypeId'
GO

CREATE TRIGGER [dbo].[tgr_TransactionReportSubscription]
    ON [dbo].[Transaction]
    FOR DELETE, INSERT, UPDATE
    AS
    BEGIN
        SET NoCount ON
        SELECT * INTO #INSERTED FROM INSERTED
        SELECT * INTO #DELETED FROM DELETED

        EXEC Sp_PublishChanges 'Transaction', 'dbo', 'TransactionId'

        DROP TABLE #INSERTED
        DROP TABLE #DELETED
    END