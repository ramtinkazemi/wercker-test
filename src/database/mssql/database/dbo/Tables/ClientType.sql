CREATE TABLE [dbo].[ClientType] (
    [ClientTypeId] INT            IDENTITY (100, 1) NOT NULL,
    [TypeName]     NVARCHAR (100) NOT NULL,
    CONSTRAINT [PK_ClientType] PRIMARY KEY CLUSTERED ([ClientTypeId] ASC)
);

