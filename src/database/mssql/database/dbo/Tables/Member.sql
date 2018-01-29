CREATE TABLE [dbo].[Member] (
    [MemberId]              INT              IDENTITY (1000000000, 1) NOT NULL,
    [ClientId]              INT              NOT NULL,
    [Status]                INT              NOT NULL,
    [DateOfBirth]           DATETIME2  
    ENCRYPTED WITH (
                COLUMN_ENCRYPTION_KEY = [PIIColumnEncryptionKey], 
                ENCRYPTION_TYPE = Deterministic, 
                ALGORITHM = 'AEAD_AES_256_CBC_HMAC_SHA_256') NULL,
    [FirstName]             NVARCHAR (50) NULL,
    [LastName]              NVARCHAR (50) NULL,
    [PostCode]              NVARCHAR (50)
    COLLATE Latin1_General_BIN2 ENCRYPTED WITH (
                COLUMN_ENCRYPTION_KEY = [PIIColumnEncryptionKey], 
                ENCRYPTION_TYPE = Deterministic, 
                ALGORITHM = 'AEAD_AES_256_CBC_HMAC_SHA_256')     NULL,
    [Mobile]                NVARCHAR (50)
    COLLATE Latin1_General_BIN2 ENCRYPTED WITH (
                COLUMN_ENCRYPTION_KEY = [PIIColumnEncryptionKey], 
                ENCRYPTION_TYPE = Deterministic, 
                ALGORITHM = 'AEAD_AES_256_CBC_HMAC_SHA_256')     NULL,
    [Email]                 NVARCHAR (200)   NULL,
    [UserPassword]          NVARCHAR (200)   NOT NULL,
    [SaltKey]               NVARCHAR (200)   NOT NULL,
    [CookieIpAddress]       NVARCHAR (200)   NULL,
    [FacebookUsername]      NVARCHAR (500)
    COLLATE Latin1_General_BIN2 ENCRYPTED WITH (
                COLUMN_ENCRYPTION_KEY = [PIIColumnEncryptionKey], 
                ENCRYPTION_TYPE = Deterministic, 
                ALGORITHM = 'AEAD_AES_256_CBC_HMAC_SHA_256') NULL,
    [AccessCode]            NVARCHAR (200)   NULL,
    [Gender]                INT
    ENCRYPTED WITH (
                COLUMN_ENCRYPTION_KEY = [PIIColumnEncryptionKey], 
                ENCRYPTION_TYPE = Deterministic, 
                ALGORITHM = 'AEAD_AES_256_CBC_HMAC_SHA_256') NULL,
    [ReceiveNewsLetter]     BIT              CONSTRAINT [DF_Member_ReceiveNewsLetter] DEFAULT ((1)) NOT NULL,
    [ClickWindowActive]     BIT              CONSTRAINT [DF_Table_1_ClickWindowISActive] DEFAULT ((1)) NOT NULL,
    [PopUpActive]           BIT              CONSTRAINT [DF_Member_PopUpActive] DEFAULT ((1)) NOT NULL,
    [IsValidated]           BIT              CONSTRAINT [DF_Table_1_ISValidated] DEFAULT ((0)) NOT NULL,
    [IsResetPassword]       BIT              CONSTRAINT [DF_Table_1_ISResetPassword] DEFAULT ((0)) NOT NULL,
    [RequiredLogin]         BIT              CONSTRAINT [DF_Member_RequiredLogin] DEFAULT ((1)) NOT NULL,
    [IsAvailable]           BIT              CONSTRAINT [DF_Member_IsAvailable] DEFAULT ((0)) NOT NULL,
    [ActivateBy]            DATETIME         NULL,
    [DateDeletedByMember]   DATETIME         NULL,
    [DateJoined]            DATETIME         CONSTRAINT [DF_Member_DateJoined] DEFAULT (getdate()) NULL,
    [HashedEmail]           NVARCHAR (500)   NULL,
    [HashedMobile]          NVARCHAR (500)   NULL,
    [MailChimpListEmailID]  NCHAR (50)       NULL,
    [DateReceiveNewsLetter] DATETIME         NULL,
    [CommunicationsEmail]   NVARCHAR (200)   NULL,
    [MemberNewId]           UNIQUEIDENTIFIER CONSTRAINT [DF_Member_MemberNewId] DEFAULT (newid()) NOT NULL,
    [HashedMemberNewId]     NVARCHAR (500)   NULL,
    [AutoCreated]           BIT              CONSTRAINT [DF_Member_AutoCreated] DEFAULT ((0)) NULL,
	[PaypalEmail]           NVARCHAR(200)    
	COLLATE Latin1_General_BIN2 ENCRYPTED WITH (
                COLUMN_ENCRYPTION_KEY = [PIIColumnEncryptionKey], 
                ENCRYPTION_TYPE = Deterministic, 
                ALGORITHM = 'AEAD_AES_256_CBC_HMAC_SHA_256') NULL,
    [CampaignId]			INT				NULL, 
    CONSTRAINT [PK_Member] PRIMARY KEY CLUSTERED ([MemberId] ASC),
    CONSTRAINT [FK_Member_Client] FOREIGN KEY ([ClientId]) REFERENCES [dbo].[Client] ([ClientId])
);


GO
CREATE NONCLUSTERED INDEX [IX_Member_ClientId]
    ON [dbo].[Member]([ClientId] ASC);


GO
CREATE NONCLUSTERED INDEX [IX_Member]
    ON [dbo].[Member]([CommunicationsEmail] ASC);


GO
CREATE NONCLUSTERED INDEX [IX_Member_1]
    ON [dbo].[Member]([CommunicationsEmail] ASC);


GO


CREATE TRIGGER
    [dbo].[tgr_UpdateMemberWoopra]
ON 
    [dbo].[Member]
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
	IF EXISTS(SELECT  MemberId,FirstName,LastName,DateOfBirth,PostCode,Gender,Mobile,FacebookUsername,DateJoined,IsValidated,[Status],Email,AccessCode FROM inserted
          EXCEPT
          SELECT MemberId,FirstName,LastName,DateOfBirth,PostCode,Gender,Mobile,FacebookUsername,DateJoined,IsValidated,[Status],Email,AccessCode FROM deleted )   
		   -- Tests for modifications to fields that we are interested in
	BEGIN
			  -- Put code here that does the work in the trigger
		INSERT INTO [dbo].[WoopraData](WoopraEntityTypeId,EntityId
								--,EntityType
				,WoopraEntityActionId,DateAdded,DatePushed)
							 SELECT 2,MemberId
							 --,'Member'
							 ,2,GETDATE(),NULL 
				FROM inserted
	END
	END
	
END --END TRIGGER



GO
CREATE TRIGGER [dbo].[tgr_UpdateCommunicationEmail]
   ON  dbo.Member
   AFTER INSERT, UPDATE
AS 
BEGIN
    SET NOCOUNT ON;
    UPDATE Member SET CommunicationsEmail = Email WHERE MemberId IN (SELECT MemberId FROM Inserted) AND  CommunicationsEmail <> Email
END

GO

CREATE TRIGGER
    [dbo].[tgr_InsertMemberWoopra]
ON 
    [dbo].[Member]
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
				INSERT INTO [dbo].[WoopraData](WoopraEntityTypeId,EntityId,WoopraEntityActionId,DateAdded,DatePushed)
							 SELECT 2,inserted.MemberId,1,GETDATE(),NULL 
				FROM inserted  
	END
	
END --END TRIGGER



GO

CREATE TRIGGER [dbo].[tgr_MemberReportSubscription]
    ON [dbo].[Member]
    FOR DELETE, INSERT, UPDATE
    AS
    BEGIN
        SET NOCOUNT ON
        --DECLARE @ST NVARCHAR(MAX)
        -- GENERATE SCRIPT FOR NON-ENCRYPTED COLUMNS
        --SELECT @ST = ISNULL(@ST + ',', '') + CT.name
        --FROM (
        --SELECT C.name
        --FROM  SYS.COLUMNS C
        --INNER JOIN SYS.TABLES T
        --    ON C.OBJECT_ID = T.OBJECT_ID 
        --INNER JOIN SYS.SCHEMAS S
        --    ON T.SCHEMA_ID = S.SCHEMA_ID
        --WHERE T.name = @TableName AND S.name=@TableSchema AND ISNULL(c.encryption_type, 0) = 0
        --) CT
        --SELECT 'SELECT ' + @ST + ' INTO #INSERTED FROM INSERTED'

        SELECT MemberId
	        ,ClientId
	        ,STATUS
	        ,FirstName
	        ,LastName
	        ,Email
	        ,UserPassword
	        ,SaltKey
	        ,CookieIpAddress
	        ,AccessCode
	        ,ReceiveNewsLetter
	        ,ClickWindowActive
	        ,PopUpActive
	        ,IsValidated
	        ,IsResetPassword
	        ,RequiredLogin
	        ,IsAvailable
	        ,ActivateBy
	        ,DateDeletedByMember
	        ,DateJoined
	        ,HashedEmail
	        ,HashedMobile
	        ,MailChimpListEmailID
	        ,DateReceiveNewsLetter
	        ,CommunicationsEmail
	        ,MemberNewId
	        ,HashedMemberNewId
	        ,AutoCreated
	        ,CampaignId
        INTO #INSERTED
        FROM INSERTED
        
        SELECT MemberId
	        ,ClientId
	        ,STATUS
	        ,FirstName
	        ,LastName
	        ,Email
	        ,UserPassword
	        ,SaltKey
	        ,CookieIpAddress
	        ,AccessCode
	        ,ReceiveNewsLetter
	        ,ClickWindowActive
	        ,PopUpActive
	        ,IsValidated
	        ,IsResetPassword
	        ,RequiredLogin
	        ,IsAvailable
	        ,ActivateBy
	        ,DateDeletedByMember
	        ,DateJoined
	        ,HashedEmail
	        ,HashedMobile
	        ,MailChimpListEmailID
	        ,DateReceiveNewsLetter
	        ,CommunicationsEmail
	        ,MemberNewId
	        ,HashedMemberNewId
	        ,AutoCreated
	        ,CampaignId
        INTO #DELETED
        FROM DELETED

        EXEC Sp_PublishChanges 'Member', 'dbo', 'MemberId'

        DROP TABLE #INSERTED
        DROP TABLE #DELETED
    END