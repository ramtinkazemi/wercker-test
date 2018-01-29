CREATE TABLE [dbo].[GstStatus] (
    [GstStatusId] INT           NOT NULL,
    [GstStatus]   NVARCHAR (50) NOT NULL,
    CONSTRAINT [PK_GstStatus] PRIMARY KEY CLUSTERED ([GstStatusId] ASC)
);

