CREATE TABLE [dbo].[ClientRewardType] (
    [ClientRewardTypeId] INT            IDENTITY (100, 1) NOT NULL,
    [RewardTypeName]     NVARCHAR (100) NOT NULL,
    CONSTRAINT [PK_ClientRewardType] PRIMARY KEY CLUSTERED ([ClientRewardTypeId] ASC)
);

