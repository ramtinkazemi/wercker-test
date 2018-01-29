CREATE TABLE [dbo].[TimeZone] (
    [TimeZoneId]    INT             IDENTITY (1000, 1) NOT NULL,
    [Abbrevation]   NVARCHAR (50)   NOT NULL,
    [ZoneName]      NVARCHAR (500)  NOT NULL,
    [GmtOffsetHour] DECIMAL (18, 2) NOT NULL,
    [StartDate]     DATETIME        NULL,
    [EndTime]       DATETIME        NULL,
    CONSTRAINT [PK_TimeZone] PRIMARY KEY CLUSTERED ([TimeZoneId] ASC)
);

