CREATE TABLE [dbo].[MerchantClientMap] (
    [MappingId]  INT IDENTITY (1, 1) NOT NULL,
    [MerchantId] INT NOT NULL,
    [ClientId]   INT NOT NULL,
    CONSTRAINT [PK_MerchantClientMap] PRIMARY KEY CLUSTERED ([MappingId] ASC),
    CONSTRAINT [FK_MerchantClientMap_Client] FOREIGN KEY ([ClientId]) REFERENCES [dbo].[Client] ([ClientId]),
    CONSTRAINT [FK_MerchantClientMap_Merchant] FOREIGN KEY ([MerchantId]) REFERENCES [dbo].[Merchant] ([MerchantId])
);


GO
CREATE NONCLUSTERED INDEX [IX_MerchantClientMap_MerchantId]
    ON [dbo].[MerchantClientMap]([MerchantId] ASC)
    INCLUDE([ClientId]);


GO
CREATE NONCLUSTERED INDEX [IX_MerchantClientMap_ClientId]
    ON [dbo].[MerchantClientMap]([ClientId] ASC)
    INCLUDE([MerchantId]);

