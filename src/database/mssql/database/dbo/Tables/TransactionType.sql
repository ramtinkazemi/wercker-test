CREATE TABLE [dbo].[TransactionType]
(
    [TransactionTypeId] INT NOT NULL IDENTITY , 
    [DescriptionShort] NVARCHAR(50) NULL, 
    [DescriptionLong] NVARCHAR(MAX) NULL, 
    [DateAdded] DATETIME2 NOT NULL CONSTRAINT [DF_TransactionType_DateAdded] DEFAULT GETDATE(), 
    CONSTRAINT [PK_TransactionType] PRIMARY KEY ([TransactionTypeId])    
)

GO
EXEC sp_addextendedproperty @name = N'MS_Description',
    @value = N'Identity id for transaction type',
    @level0type = N'SCHEMA',
    @level0name = N'dbo',
    @level1type = N'TABLE',
    @level1name = N'TransactionType',
    @level2type = N'COLUMN',
    @level2name = 'TransactionTypeId'
GO
EXEC sp_addextendedproperty @name = N'MS_Description',
    @value = N'Short description',
    @level0type = N'SCHEMA',
    @level0name = N'dbo',
    @level1type = N'TABLE',
    @level1name = N'TransactionType',
    @level2type = N'COLUMN',
    @level2name = N'DescriptionShort'
GO
EXEC sp_addextendedproperty @name = N'MS_Description',
    @value = N'Long description',
    @level0type = N'SCHEMA',
    @level0name = N'dbo',
    @level1type = N'TABLE',
    @level1name = N'TransactionType',
    @level2type = N'COLUMN',
    @level2name = N'DescriptionLong'
GO
EXEC sp_addextendedproperty @name = N'MS_Description',
    @value = N'Date added in local time(default to current time)',
    @level0type = N'SCHEMA',
    @level0name = N'dbo',
    @level1type = N'TABLE',
    @level1name = N'TransactionType',
    @level2type = N'COLUMN',
    @level2name = N'DateAdded'