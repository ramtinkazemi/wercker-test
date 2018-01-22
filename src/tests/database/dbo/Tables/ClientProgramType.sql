CREATE TABLE [dbo].[ClientProgramType] (
    [ClientProgramTypeId] INT            IDENTITY (100, 1) NOT NULL,
    [ProgramName]         NVARCHAR (100) NOT NULL,
    CONSTRAINT [PK_ClientProgramType] PRIMARY KEY CLUSTERED ([ClientProgramTypeId] ASC)
);

