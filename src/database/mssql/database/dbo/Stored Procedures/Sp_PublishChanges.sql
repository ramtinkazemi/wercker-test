CREATE PROCEDURE [dbo].[Sp_PublishChanges]
    @TableName VARCHAR(200),
    @TableSchema VARCHAR(200),
    @PrimaryKeyName VARCHAR(200)
AS
    DECLARE @OriginTableAlias VARCHAR(200)
    DECLARE @NewTableAlias VARCHAR(200)
    DECLARE @JsonBeforeElement VARCHAR(200)
    DECLARE @JsonAfterElement VARCHAR(200)
    DECLARE @FieldsStatement VARCHAR(MAX)
    DECLARE @BeforeFieldsStatement VARCHAR(MAX)
    DECLARE @AfterFieldsStatement VARCHAR(MAX)
    DECLARE @ModifiedFieldStatement VARCHAR(MAX)
    DECLARE @DynamicSQLStatement NVARCHAR(MAX)
    DECLARE @DeletedTableName VARCHAR(200)
    DECLARE @InsertedTableName VARCHAR(200)
    DECLARE @JsonAttributeStatement VARCHAR(MAX)
    SET @OriginTableAlias = 'origin.'
    SET @NewTableAlias = 'new.'
    SET @JsonBeforeElement = 'before.'
    SET @JsonAfterElement = 'after.'
    SET @InsertedTableName = '#INSERTED'
    SET @DeletedTableName = '#DELETED'


    SELECT 
        @BeforeFieldsStatement = ISNULL(@BeforeFieldsStatement + ',', '') + BeforeField,
        @AfterFieldsStatement = ISNULL(@AfterFieldsStatement + ',', '') + AfterField,
        --@JsonAttributeStatement = ISNULL(@JsonAttributeStatement + ',', '') + JsonAttribute,
        @FieldsStatement = @BeforeFieldsStatement + ',' + @AfterFieldsStatement,
        @ModifiedFieldStatement = ISNULL(@ModifiedFieldStatement + ' union all ', '') + ModifiedField
    FROM(
    SELECT @OriginTableAlias + C.name + ' AS ''' + @JsonBeforeElement + C.name + '''' AS BeforeField, 
        @NewTableAlias + C.name + ' AS ''' + @JsonAfterElement + C.name + '''' AS AfterField,
        --C.name + ' ' + DATA_TYPE + ISNULL('(' + CAST(CHARACTER_MAXIMUM_LENGTH AS VARCHAR(50)) + ')', '') + ''' $.' + C.name + '''' AS JsonAttribute,
        'SELECT CASE WHEN ISNULL(' + @OriginTableAlias + C.name + ', '''') <> ISNULL(' + @NewTableAlias + C.name + ', '''') THEN '''+ C.name + ''' ELSE NULL END AS FieldName' AS ModifiedField
    FROM  SYS.COLUMNS C
    INNER JOIN SYS.TABLES T
        ON C.OBJECT_ID = T.OBJECT_ID 
    INNER JOIN SYS.SCHEMAS S
        ON T.SCHEMA_ID = S.SCHEMA_ID
    WHERE T.name = @TableName AND S.name=@TableSchema AND ISNULL(c.encryption_type, 0) = 0
    ) t;

    IF EXISTS(SELECT 1 FROM #INSERTED) AND EXISTS(SELECT 1 FROM #DELETED)
    BEGIN
        SELECT @DynamicSQLStatement = '
            SELECT new.' + @PrimaryKeyName + ' AS Id,  
                ''UPDATE'' AS [Action],' + 
                @BeforeFieldsStatement + ',' + @AfterFieldsStatement + ',
                (
                    SELECT * FROM(' + 
                    @ModifiedFieldStatement + '
                    ) c WHERE c.FieldName IS NOT NULL FOR JSON PATH
                ) AS ModifiedFields
            FROM ' + @InsertedTableName  + ' new INNER JOIN ' + @DeletedTableName + ' origin ON new.' + @PrimaryKeyName + ' = ' + 'origin.' + @PrimaryKeyName + '
            FOR JSON PATH'
    END
    ELSE IF EXISTS(SELECT 1 FROM #INSERTED)
    BEGIN
            SELECT @DynamicSQLStatement = '
            SELECT new.' + @PrimaryKeyName + ' AS Id,  
                ''INSERT'' AS [Action]
            FROM ' + @InsertedTableName + ' new
            FOR JSON PATH'
    END
    ELSE IF EXISTS(SELECT 1 FROM #DELETED)
    BEGIN
            SELECT @DynamicSQLStatement = '
            SELECT origin.' + @PrimaryKeyName + ' AS Id,  
                ''DELETED'' AS [Action] ,' + @BeforeFieldsStatement + '
            FROM ' + @DeletedTableName + ' origin
            FOR JSON PATH'
    END

    IF @DynamicSQLStatement IS NOT NULL
    BEGIN
        SELECT @DynamicSQLStatement = 'INSERT INTO [dbo].[ReportSubscription](TableName, Changes) SELECT '''+ @TableName + ''', (' + @DynamicSQLStatement + ')'
    END
    EXEC SP_EXECUTESQL  @DynamicSQLStatement
RETURN 0
