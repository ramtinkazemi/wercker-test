CREATE TABLE [dbo].[MemberClicks] (
    [ClickId]             BIGINT         IDENTITY (1, 1) NOT NULL,
    [MemberId]            INT            NOT NULL,
    [MerchantId]          INT            NOT NULL,
    [ItemType]            NVARCHAR (50)  NULL,
    [ItemId]              INT            NULL,
    [DateCreated]         DATETIME       NOT NULL,
    [IpAddress]           VARCHAR (500)  NULL,
    [RedirectionLinkUsed] VARCHAR (1000) NULL,
    [AdBlockerEnabled]    BIT            NULL,
    [UserAgent]           NVARCHAR (500) NULL,
    [CashbackOffer]       NVARCHAR (100) NULL,
    [CampaignId]		  INT			 NULL, 
    CONSTRAINT [PK_MemberClicks] PRIMARY KEY CLUSTERED ([ClickId] ASC),
    CONSTRAINT [FK_MemberClicks_Member] FOREIGN KEY ([MemberId]) REFERENCES [dbo].[Member] ([MemberId])
);


GO
CREATE NONCLUSTERED INDEX [NonClusteredIndex-20160220-120816]
    ON [dbo].[MemberClicks]([MemberId] ASC, [ItemId] ASC)
    INCLUDE([MerchantId]);


GO


CREATE TRIGGER
    [dbo].[tgr_InsertMemberClicksWoopra]
ON 
    [dbo].[MemberClicks]
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
							 SELECT 4,ClickId
							 --,'Member'
							 ,1,GETDATE(),NULL 
				FROM inserted
	END
	
END --END TRIGGER






