CREATE TABLE [dbo].[ClientLayoutTheme] (
    [ClientLayoutThemeId] INT            IDENTITY (100, 1) NOT NULL,
    [ThemeName]           NVARCHAR (100) NOT NULL,
    CONSTRAINT [PK_ClientLayoutTheme] PRIMARY KEY CLUSTERED ([ClientLayoutThemeId] ASC)
);

