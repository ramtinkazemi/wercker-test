CREATE TABLE [dbo].[TransactionApprovalReport] (
    [TransactionReportId]     INT            IDENTITY (1, 1) NOT NULL,
    [TransactionId]           INT            NOT NULL,
    [MemberId]                INT            NOT NULL,
    [ClientId]                INT            NOT NULL,
    [MerchantId]              INT            NOT NULL,
    [SaleValue]               DECIMAL (8, 2) NULL,
    [MemberCashback]          DECIMAL (8, 2) NULL,
    [BatchId]                 NVARCHAR (20)  NOT NULL,
    [TransactionReportTypeId] INT            NOT NULL,
    [Comment]                 NVARCHAR (200) NULL,
    [NetworkId]               INT            NOT NULL,
    CONSTRAINT [PK_TransactionReport] PRIMARY KEY CLUSTERED ([TransactionReportId] ASC),
    CONSTRAINT [FK_TransactionReport_Member] FOREIGN KEY ([MemberId]) REFERENCES [dbo].[Member] ([MemberId]),
    CONSTRAINT [FK_TransactionReport_Merchant] FOREIGN KEY ([MerchantId]) REFERENCES [dbo].[Merchant] ([MerchantId]),
    CONSTRAINT [FK_TransactionReport_Network] FOREIGN KEY ([NetworkId]) REFERENCES [dbo].[Network] ([NetworkId]),
    CONSTRAINT [FK_TransactionReport_Transaction] FOREIGN KEY ([TransactionId]) REFERENCES [dbo].[Transaction] ([TransactionId]),
    CONSTRAINT [FK_TransactionReport_TransactionReportType] FOREIGN KEY ([TransactionReportTypeId]) REFERENCES [dbo].[TransactionReportType] ([TransactionReportTypeId])
);

