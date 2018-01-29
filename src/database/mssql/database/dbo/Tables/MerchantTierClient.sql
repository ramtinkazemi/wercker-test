CREATE TABLE [dbo].[MerchantTierClient] (
    [ClientTierId]   INT             IDENTITY (1, 1) NOT NULL,
    [MerchantTierId] INT             NOT NULL,
    [ClientId]       INT             NOT NULL,
    [StartDate]      DATETIME        NOT NULL,
    [EndDate]        DATETIME        NOT NULL,
    [ClientComm]     DECIMAL (18, 2) NOT NULL,
    [MemberComm]     DECIMAL (18, 2) NOT NULL,
    [Status]         INT             NOT NULL,
    CONSTRAINT [PK_ClientTier] PRIMARY KEY CLUSTERED ([ClientTierId] ASC),
    CONSTRAINT [FK_ClientTier_MerchantTier] FOREIGN KEY ([MerchantTierId]) REFERENCES [dbo].[MerchantTier] ([MerchantTierId]),
    CONSTRAINT [FK_MerchantTierClient_Client] FOREIGN KEY ([ClientId]) REFERENCES [dbo].[Client] ([ClientId])
);


GO
CREATE NONCLUSTERED INDEX [IX_MerchantTierClient]
    ON [dbo].[MerchantTierClient]([ClientId] ASC);


GO
CREATE NONCLUSTERED INDEX [IX_MerchantTierClient_MerchantTierId]
    ON [dbo].[MerchantTierClient]([MerchantTierId] ASC);


GO
CREATE NONCLUSTERED INDEX [NonClusteredIndex-Display]
    ON [dbo].[MerchantTierClient]([MerchantTierId] ASC, [ClientId] ASC, [Status] DESC);


GO
CREATE NONCLUSTERED INDEX [NonClusteredIndex-20141115-232630]
    ON [dbo].[MerchantTierClient]([ClientId] ASC, [StartDate] ASC, [EndDate] ASC, [Status] ASC)
    INCLUDE([ClientTierId], [MerchantTierId], [ClientComm], [MemberComm]);

