CREATE TABLE [dbo].[TransactionStatus] (
    [TransactionStatusId] INT           NOT NULL,
    [TransactionStatus]   NVARCHAR (50) NOT NULL,
    CONSTRAINT [PK_TransactionStatus] PRIMARY KEY CLUSTERED ([TransactionStatusId] ASC)
);

