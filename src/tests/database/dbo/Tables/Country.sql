CREATE TABLE [dbo].[Country] (
    [CountryId]   INT           IDENTITY (1, 1) NOT NULL,
    [CountryCode] NVARCHAR (50) NOT NULL,
    [CountryName] NVARCHAR (50) NOT NULL,
    [Status]      INT           CONSTRAINT [DF_Country_IsActive] DEFAULT ((0)) NOT NULL,
    CONSTRAINT [PK_Country] PRIMARY KEY CLUSTERED ([CountryId] ASC)
);

