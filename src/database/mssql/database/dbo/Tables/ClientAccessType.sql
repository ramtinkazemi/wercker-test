CREATE TABLE [dbo].[ClientAccessType] (
    [ClientAccessTypeId] INT            IDENTITY (100, 1) NOT NULL,
    [AccessTypeName]     NVARCHAR (100) NOT NULL,
    CONSTRAINT [PK_ClientAccessType] PRIMARY KEY CLUSTERED ([ClientAccessTypeId] ASC)
);

