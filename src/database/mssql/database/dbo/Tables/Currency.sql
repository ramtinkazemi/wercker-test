CREATE TABLE [dbo].[Currency] (
    [CurrencyId]   INT           IDENTITY (100, 1) NOT NULL,
    [CurrencyCode] NVARCHAR (50) NOT NULL,
    [CurrencyName] NVARCHAR (50) NOT NULL,
    CONSTRAINT [PK_Currency] PRIMARY KEY CLUSTERED ([CurrencyId] ASC)
);

