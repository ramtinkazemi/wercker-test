CREATE TABLE [dbo].[Network] (
    [NetworkId]           INT            IDENTITY (1000000, 1) NOT NULL,
    [NetworkName]         NVARCHAR (500) NOT NULL,
    [TrackingHolder]      NVARCHAR (500) NOT NULL,
    [DeepLinkHolder]      NVARCHAR (500) NULL,
    [NetworkKey]          NVARCHAR (50)  NULL,
    [Status]              INT            NOT NULL,
    [TimeZoneId]          INT            NULL,
    [ClickWindowCheck]    BIT            CONSTRAINT [DF_Network_ClickWindowCheck] DEFAULT ((0)) NOT NULL,
    [DuplicateTransCheck] BIT            CONSTRAINT [DF_Network_DuplicateTransCheck_1] DEFAULT ((0)) NOT NULL,
    [GstStatusId]         INT            CONSTRAINT [DF_Network_GstStatusId_1] DEFAULT ((100)) NOT NULL,
    CONSTRAINT [PK_Network] PRIMARY KEY CLUSTERED ([NetworkId] ASC),
    CONSTRAINT [FK_Network_TimeZone] FOREIGN KEY ([TimeZoneId]) REFERENCES [dbo].[TimeZone] ([TimeZoneId])
);

