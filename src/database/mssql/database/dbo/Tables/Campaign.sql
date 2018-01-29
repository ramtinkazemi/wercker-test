CREATE TABLE [dbo].[Campaign] (
    [CampaignId]   INT             IDENTITY (1, 1) NOT NULL,
    [Name]         NVARCHAR (128)  NOT NULL DEFAULT '',
    [Campaign]     NVARCHAR (128)  NOT NULL DEFAULT '',
    [Source]       NVARCHAR (128)  NOT NULL DEFAULT '',
    [Medium]       NVARCHAR (128)  NOT NULL DEFAULT '',
    [Content]      NVARCHAR (128)  NOT NULL DEFAULT '',
    [Pixel]        NVARCHAR (1000) NOT NULL DEFAULT '', 
	[DateCreated]  DATETIME        NOT NULL	DEFAULT GETDATE(),
    CONSTRAINT [PK_Campaign] PRIMARY KEY CLUSTERED ([CampaignId] ASC)
);

