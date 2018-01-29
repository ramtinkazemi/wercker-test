CREATE TABLE [dbo].[ReportSubscription]
(
    [Id] INT NOT NULL PRIMARY KEY IDENTITY(1,1), 
    [TableName] VARCHAR(200) NOT NULL, 
    [Changes] NVARCHAR(MAX) NOT NULL, 
    [LastModifiedDate] DATETIME2 CONSTRAINT [DF_ReportSubscription_LastModifiedDate] NOT NULL DEFAULT GETDATE()
)
