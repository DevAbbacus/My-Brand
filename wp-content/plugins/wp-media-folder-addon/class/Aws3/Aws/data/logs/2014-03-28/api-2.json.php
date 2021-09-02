<?php

// This file was auto-generated from sdk-root/src/data/logs/2014-03-28/api-2.json
return ['version' => '2.0', 'metadata' => ['apiVersion' => '2014-03-28', 'endpointPrefix' => 'logs', 'jsonVersion' => '1.1', 'protocol' => 'json', 'serviceFullName' => 'Amazon CloudWatch Logs', 'serviceId' => 'CloudWatch Logs', 'signatureVersion' => 'v4', 'targetPrefix' => 'Logs_20140328', 'uid' => 'logs-2014-03-28'], 'operations' => ['AssociateKmsKey' => ['name' => 'AssociateKmsKey', 'http' => ['method' => 'POST', 'requestUri' => '/'], 'input' => ['shape' => 'AssociateKmsKeyRequest'], 'errors' => [['shape' => 'InvalidParameterException'], ['shape' => 'ResourceNotFoundException'], ['shape' => 'OperationAbortedException'], ['shape' => 'ServiceUnavailableException']]], 'CancelExportTask' => ['name' => 'CancelExportTask', 'http' => ['method' => 'POST', 'requestUri' => '/'], 'input' => ['shape' => 'CancelExportTaskRequest'], 'errors' => [['shape' => 'InvalidParameterException'], ['shape' => 'ResourceNotFoundException'], ['shape' => 'InvalidOperationException'], ['shape' => 'ServiceUnavailableException']]], 'CreateExportTask' => ['name' => 'CreateExportTask', 'http' => ['method' => 'POST', 'requestUri' => '/'], 'input' => ['shape' => 'CreateExportTaskRequest'], 'output' => ['shape' => 'CreateExportTaskResponse'], 'errors' => [['shape' => 'InvalidParameterException'], ['shape' => 'LimitExceededException'], ['shape' => 'OperationAbortedException'], ['shape' => 'ServiceUnavailableException'], ['shape' => 'ResourceNotFoundException'], ['shape' => 'ResourceAlreadyExistsException']]], 'CreateLogGroup' => ['name' => 'CreateLogGroup', 'http' => ['method' => 'POST', 'requestUri' => '/'], 'input' => ['shape' => 'CreateLogGroupRequest'], 'errors' => [['shape' => 'InvalidParameterException'], ['shape' => 'ResourceAlreadyExistsException'], ['shape' => 'LimitExceededException'], ['shape' => 'OperationAbortedException'], ['shape' => 'ServiceUnavailableException']]], 'CreateLogStream' => ['name' => 'CreateLogStream', 'http' => ['method' => 'POST', 'requestUri' => '/'], 'input' => ['shape' => 'CreateLogStreamRequest'], 'errors' => [['shape' => 'InvalidParameterException'], ['shape' => 'ResourceAlreadyExistsException'], ['shape' => 'ResourceNotFoundException'], ['shape' => 'ServiceUnavailableException']]], 'DeleteDestination' => ['name' => 'DeleteDestination', 'http' => ['method' => 'POST', 'requestUri' => '/'], 'input' => ['shape' => 'DeleteDestinationRequest'], 'errors' => [['shape' => 'InvalidParameterException'], ['shape' => 'ResourceNotFoundException'], ['shape' => 'OperationAbortedException'], ['shape' => 'ServiceUnavailableException']]], 'DeleteLogGroup' => ['name' => 'DeleteLogGroup', 'http' => ['method' => 'POST', 'requestUri' => '/'], 'input' => ['shape' => 'DeleteLogGroupRequest'], 'errors' => [['shape' => 'InvalidParameterException'], ['shape' => 'ResourceNotFoundException'], ['shape' => 'OperationAbortedException'], ['shape' => 'ServiceUnavailableException']]], 'DeleteLogStream' => ['name' => 'DeleteLogStream', 'http' => ['method' => 'POST', 'requestUri' => '/'], 'input' => ['shape' => 'DeleteLogStreamRequest'], 'errors' => [['shape' => 'InvalidParameterException'], ['shape' => 'ResourceNotFoundException'], ['shape' => 'OperationAbortedException'], ['shape' => 'ServiceUnavailableException']]], 'DeleteMetricFilter' => ['name' => 'DeleteMetricFilter', 'http' => ['method' => 'POST', 'requestUri' => '/'], 'input' => ['shape' => 'DeleteMetricFilterRequest'], 'errors' => [['shape' => 'InvalidParameterException'], ['shape' => 'ResourceNotFoundException'], ['shape' => 'OperationAbortedException'], ['shape' => 'ServiceUnavailableException']]], 'DeleteResourcePolicy' => ['name' => 'DeleteResourcePolicy', 'http' => ['method' => 'POST', 'requestUri' => '/'], 'input' => ['shape' => 'DeleteResourcePolicyRequest'], 'errors' => [['shape' => 'InvalidParameterException'], ['shape' => 'ResourceNotFoundException'], ['shape' => 'ServiceUnavailableException']]], 'DeleteRetentionPolicy' => ['name' => 'DeleteRetentionPolicy', 'http' => ['method' => 'POST', 'requestUri' => '/'], 'input' => ['shape' => 'DeleteRetentionPolicyRequest'], 'errors' => [['shape' => 'InvalidParameterException'], ['shape' => 'ResourceNotFoundException'], ['shape' => 'OperationAbortedException'], ['shape' => 'ServiceUnavailableException']]], 'DeleteSubscriptionFilter' => ['name' => 'DeleteSubscriptionFilter', 'http' => ['method' => 'POST', 'requestUri' => '/'], 'input' => ['shape' => 'DeleteSubscriptionFilterRequest'], 'errors' => [['shape' => 'InvalidParameterException'], ['shape' => 'ResourceNotFoundException'], ['shape' => 'OperationAbortedException'], ['shape' => 'ServiceUnavailableException']]], 'DescribeDestinations' => ['name' => 'DescribeDestinations', 'http' => ['method' => 'POST', 'requestUri' => '/'], 'input' => ['shape' => 'DescribeDestinationsRequest'], 'output' => ['shape' => 'DescribeDestinationsResponse'], 'errors' => [['shape' => 'InvalidParameterException'], ['shape' => 'ServiceUnavailableException']]], 'DescribeExportTasks' => ['name' => 'DescribeExportTasks', 'http' => ['method' => 'POST', 'requestUri' => '/'], 'input' => ['shape' => 'DescribeExportTasksRequest'], 'output' => ['shape' => 'DescribeExportTasksResponse'], 'errors' => [['shape' => 'InvalidParameterException'], ['shape' => 'ServiceUnavailableException']]], 'DescribeLogGroups' => ['name' => 'DescribeLogGroups', 'http' => ['method' => 'POST', 'requestUri' => '/'], 'input' => ['shape' => 'DescribeLogGroupsRequest'], 'output' => ['shape' => 'DescribeLogGroupsResponse'], 'errors' => [['shape' => 'InvalidParameterException'], ['shape' => 'ServiceUnavailableException']]], 'DescribeLogStreams' => ['name' => 'DescribeLogStreams', 'http' => ['method' => 'POST', 'requestUri' => '/'], 'input' => ['shape' => 'DescribeLogStreamsRequest'], 'output' => ['shape' => 'DescribeLogStreamsResponse'], 'errors' => [['shape' => 'InvalidParameterException'], ['shape' => 'ResourceNotFoundException'], ['shape' => 'ServiceUnavailableException']]], 'DescribeMetricFilters' => ['name' => 'DescribeMetricFilters', 'http' => ['method' => 'POST', 'requestUri' => '/'], 'input' => ['shape' => 'DescribeMetricFiltersRequest'], 'output' => ['shape' => 'DescribeMetricFiltersResponse'], 'errors' => [['shape' => 'InvalidParameterException'], ['shape' => 'ResourceNotFoundException'], ['shape' => 'ServiceUnavailableException']]], 'DescribeQueries' => ['name' => 'DescribeQueries', 'http' => ['method' => 'POST', 'requestUri' => '/'], 'input' => ['shape' => 'DescribeQueriesRequest'], 'output' => ['shape' => 'DescribeQueriesResponse'], 'errors' => [['shape' => 'InvalidParameterException'], ['shape' => 'ResourceNotFoundException'], ['shape' => 'ServiceUnavailableException']]], 'DescribeResourcePolicies' => ['name' => 'DescribeResourcePolicies', 'http' => ['method' => 'POST', 'requestUri' => '/'], 'input' => ['shape' => 'DescribeResourcePoliciesRequest'], 'output' => ['shape' => 'DescribeResourcePoliciesResponse'], 'errors' => [['shape' => 'InvalidParameterException'], ['shape' => 'ServiceUnavailableException']]], 'DescribeSubscriptionFilters' => ['name' => 'DescribeSubscriptionFilters', 'http' => ['method' => 'POST', 'requestUri' => '/'], 'input' => ['shape' => 'DescribeSubscriptionFiltersRequest'], 'output' => ['shape' => 'DescribeSubscriptionFiltersResponse'], 'errors' => [['shape' => 'InvalidParameterException'], ['shape' => 'ResourceNotFoundException'], ['shape' => 'ServiceUnavailableException']]], 'DisassociateKmsKey' => ['name' => 'DisassociateKmsKey', 'http' => ['method' => 'POST', 'requestUri' => '/'], 'input' => ['shape' => 'DisassociateKmsKeyRequest'], 'errors' => [['shape' => 'InvalidParameterException'], ['shape' => 'ResourceNotFoundException'], ['shape' => 'OperationAbortedException'], ['shape' => 'ServiceUnavailableException']]], 'FilterLogEvents' => ['name' => 'FilterLogEvents', 'http' => ['method' => 'POST', 'requestUri' => '/'], 'input' => ['shape' => 'FilterLogEventsRequest'], 'output' => ['shape' => 'FilterLogEventsResponse'], 'errors' => [['shape' => 'InvalidParameterException'], ['shape' => 'ResourceNotFoundException'], ['shape' => 'ServiceUnavailableException']]], 'GetLogEvents' => ['name' => 'GetLogEvents', 'http' => ['method' => 'POST', 'requestUri' => '/'], 'input' => ['shape' => 'GetLogEventsRequest'], 'output' => ['shape' => 'GetLogEventsResponse'], 'errors' => [['shape' => 'InvalidParameterException'], ['shape' => 'ResourceNotFoundException'], ['shape' => 'ServiceUnavailableException']]], 'GetLogGroupFields' => ['name' => 'GetLogGroupFields', 'http' => ['method' => 'POST', 'requestUri' => '/'], 'input' => ['shape' => 'GetLogGroupFieldsRequest'], 'output' => ['shape' => 'GetLogGroupFieldsResponse'], 'errors' => [['shape' => 'InvalidParameterException'], ['shape' => 'LimitExceededException'], ['shape' => 'ResourceNotFoundException'], ['shape' => 'ServiceUnavailableException']]], 'GetLogRecord' => ['name' => 'GetLogRecord', 'http' => ['method' => 'POST', 'requestUri' => '/'], 'input' => ['shape' => 'GetLogRecordRequest'], 'output' => ['shape' => 'GetLogRecordResponse'], 'errors' => [['shape' => 'InvalidParameterException'], ['shape' => 'LimitExceededException'], ['shape' => 'ResourceNotFoundException'], ['shape' => 'ServiceUnavailableException']]], 'GetQueryResults' => ['name' => 'GetQueryResults', 'http' => ['method' => 'POST', 'requestUri' => '/'], 'input' => ['shape' => 'GetQueryResultsRequest'], 'output' => ['shape' => 'GetQueryResultsResponse'], 'errors' => [['shape' => 'InvalidParameterException'], ['shape' => 'ResourceNotFoundException'], ['shape' => 'ServiceUnavailableException']]], 'ListTagsLogGroup' => ['name' => 'ListTagsLogGroup', 'http' => ['method' => 'POST', 'requestUri' => '/'], 'input' => ['shape' => 'ListTagsLogGroupRequest'], 'output' => ['shape' => 'ListTagsLogGroupResponse'], 'errors' => [['shape' => 'ResourceNotFoundException'], ['shape' => 'ServiceUnavailableException']]], 'PutDestination' => ['name' => 'PutDestination', 'http' => ['method' => 'POST', 'requestUri' => '/'], 'input' => ['shape' => 'PutDestinationRequest'], 'output' => ['shape' => 'PutDestinationResponse'], 'errors' => [['shape' => 'InvalidParameterException'], ['shape' => 'OperationAbortedException'], ['shape' => 'ServiceUnavailableException']]], 'PutDestinationPolicy' => ['name' => 'PutDestinationPolicy', 'http' => ['method' => 'POST', 'requestUri' => '/'], 'input' => ['shape' => 'PutDestinationPolicyRequest'], 'errors' => [['shape' => 'InvalidParameterException'], ['shape' => 'OperationAbortedException'], ['shape' => 'ServiceUnavailableException']]], 'PutLogEvents' => ['name' => 'PutLogEvents', 'http' => ['method' => 'POST', 'requestUri' => '/'], 'input' => ['shape' => 'PutLogEventsRequest'], 'output' => ['shape' => 'PutLogEventsResponse'], 'errors' => [['shape' => 'InvalidParameterException'], ['shape' => 'InvalidSequenceTokenException'], ['shape' => 'DataAlreadyAcceptedException'], ['shape' => 'ResourceNotFoundException'], ['shape' => 'ServiceUnavailableException'], ['shape' => 'UnrecognizedClientException']]], 'PutMetricFilter' => ['name' => 'PutMetricFilter', 'http' => ['method' => 'POST', 'requestUri' => '/'], 'input' => ['shape' => 'PutMetricFilterRequest'], 'errors' => [['shape' => 'InvalidParameterException'], ['shape' => 'ResourceNotFoundException'], ['shape' => 'OperationAbortedException'], ['shape' => 'LimitExceededException'], ['shape' => 'ServiceUnavailableException']]], 'PutResourcePolicy' => ['name' => 'PutResourcePolicy', 'http' => ['method' => 'POST', 'requestUri' => '/'], 'input' => ['shape' => 'PutResourcePolicyRequest'], 'output' => ['shape' => 'PutResourcePolicyResponse'], 'errors' => [['shape' => 'InvalidParameterException'], ['shape' => 'LimitExceededException'], ['shape' => 'ServiceUnavailableException']]], 'PutRetentionPolicy' => ['name' => 'PutRetentionPolicy', 'http' => ['method' => 'POST', 'requestUri' => '/'], 'input' => ['shape' => 'PutRetentionPolicyRequest'], 'errors' => [['shape' => 'InvalidParameterException'], ['shape' => 'ResourceNotFoundException'], ['shape' => 'OperationAbortedException'], ['shape' => 'ServiceUnavailableException']]], 'PutSubscriptionFilter' => ['name' => 'PutSubscriptionFilter', 'http' => ['method' => 'POST', 'requestUri' => '/'], 'input' => ['shape' => 'PutSubscriptionFilterRequest'], 'errors' => [['shape' => 'InvalidParameterException'], ['shape' => 'ResourceNotFoundException'], ['shape' => 'OperationAbortedException'], ['shape' => 'LimitExceededException'], ['shape' => 'ServiceUnavailableException']]], 'StartQuery' => ['name' => 'StartQuery', 'http' => ['method' => 'POST', 'requestUri' => '/'], 'input' => ['shape' => 'StartQueryRequest'], 'output' => ['shape' => 'StartQueryResponse'], 'errors' => [['shape' => 'MalformedQueryException'], ['shape' => 'InvalidParameterException'], ['shape' => 'LimitExceededException'], ['shape' => 'ResourceNotFoundException'], ['shape' => 'ServiceUnavailableException']]], 'StopQuery' => ['name' => 'StopQuery', 'http' => ['method' => 'POST', 'requestUri' => '/'], 'input' => ['shape' => 'StopQueryRequest'], 'output' => ['shape' => 'StopQueryResponse'], 'errors' => [['shape' => 'InvalidParameterException'], ['shape' => 'ResourceNotFoundException'], ['shape' => 'ServiceUnavailableException']]], 'TagLogGroup' => ['name' => 'TagLogGroup', 'http' => ['method' => 'POST', 'requestUri' => '/'], 'input' => ['shape' => 'TagLogGroupRequest'], 'errors' => [['shape' => 'ResourceNotFoundException'], ['shape' => 'InvalidParameterException']]], 'TestMetricFilter' => ['name' => 'TestMetricFilter', 'http' => ['method' => 'POST', 'requestUri' => '/'], 'input' => ['shape' => 'TestMetricFilterRequest'], 'output' => ['shape' => 'TestMetricFilterResponse'], 'errors' => [['shape' => 'InvalidParameterException'], ['shape' => 'ServiceUnavailableException']]], 'UntagLogGroup' => ['name' => 'UntagLogGroup', 'http' => ['method' => 'POST', 'requestUri' => '/'], 'input' => ['shape' => 'UntagLogGroupRequest'], 'errors' => [['shape' => 'ResourceNotFoundException']]]], 'shapes' => ['AccessPolicy' => ['type' => 'string', 'min' => 1], 'Arn' => ['type' => 'string'], 'AssociateKmsKeyRequest' => ['type' => 'structure', 'required' => ['logGroupName', 'kmsKeyId'], 'members' => ['logGroupName' => ['shape' => 'LogGroupName'], 'kmsKeyId' => ['shape' => 'KmsKeyId']]], 'CancelExportTaskRequest' => ['type' => 'structure', 'required' => ['taskId'], 'members' => ['taskId' => ['shape' => 'ExportTaskId']]], 'CreateExportTaskRequest' => ['type' => 'structure', 'required' => ['logGroupName', 'from', 'to', 'destination'], 'members' => ['taskName' => ['shape' => 'ExportTaskName'], 'logGroupName' => ['shape' => 'LogGroupName'], 'logStreamNamePrefix' => ['shape' => 'LogStreamName'], 'from' => ['shape' => 'Timestamp'], 'to' => ['shape' => 'Timestamp'], 'destination' => ['shape' => 'ExportDestinationBucket'], 'destinationPrefix' => ['shape' => 'ExportDestinationPrefix']]], 'CreateExportTaskResponse' => ['type' => 'structure', 'members' => ['taskId' => ['shape' => 'ExportTaskId']]], 'CreateLogGroupRequest' => ['type' => 'structure', 'required' => ['logGroupName'], 'members' => ['logGroupName' => ['shape' => 'LogGroupName'], 'kmsKeyId' => ['shape' => 'KmsKeyId'], 'tags' => ['shape' => 'Tags']]], 'CreateLogStreamRequest' => ['type' => 'structure', 'required' => ['logGroupName', 'logStreamName'], 'members' => ['logGroupName' => ['shape' => 'LogGroupName'], 'logStreamName' => ['shape' => 'LogStreamName']]], 'DataAlreadyAcceptedException' => ['type' => 'structure', 'members' => ['expectedSequenceToken' => ['shape' => 'SequenceToken']], 'exception' => \true], 'Days' => ['type' => 'integer'], 'DefaultValue' => ['type' => 'double'], 'DeleteDestinationRequest' => ['type' => 'structure', 'required' => ['destinationName'], 'members' => ['destinationName' => ['shape' => 'DestinationName']]], 'DeleteLogGroupRequest' => ['type' => 'structure', 'required' => ['logGroupName'], 'members' => ['logGroupName' => ['shape' => 'LogGroupName']]], 'DeleteLogStreamRequest' => ['type' => 'structure', 'required' => ['logGroupName', 'logStreamName'], 'members' => ['logGroupName' => ['shape' => 'LogGroupName'], 'logStreamName' => ['shape' => 'LogStreamName']]], 'DeleteMetricFilterRequest' => ['type' => 'structure', 'required' => ['logGroupName', 'filterName'], 'members' => ['logGroupName' => ['shap�&��U  �&��U                  ����U          @�"��U  ('��U          �&��U   @      �&��U          ype' => 'structure', 'members' => ['policyName' => ['shape' => 'PolicyName']]], 'DeleteRetentionPolicyRequest' => ['type' => 'structure', 'required' => ['logGroupName'], 'members' => ['logGroupName' => ['shape' => 'LogGroupName']]], 'DeleteSubscriptionFilterRequest' => ['type' => 'structure', 'required' => ['logGroupName', 'filterName'], 'members' => ['logGroupName' => ['shape' => 'LogGroupName'], 'filterName' => ['shape' => 'FilterName']]], 'Descending' => ['type' => 'boolean'], 'DescribeDestinationsRequest' => ['type' => 'structure', 'members' => ['DestinationNamePrefix' => ['shape' => 'DestinationName'], 'nextToken' => ['shape' => 'NextToken'], 'limit' => ['shape' => 'DescribeLimit']]], 'DescribeDestinationsResponse' => ['type' => 'structure', 'members' => ['destinations' => ['shape' => 'Destinations'], 'nextToken' => ['shape' => 'NextToken']]], 'DescribeExportTasksRequest' => ['type' => 'structure', 'members' => ['taskId' => ['shape' => 'ExportTaskId'], 'statusCode' => ['shape' => 'ExportTaskStatusCode'], 'nextToken' => ['shape' => 'NextToken'], 'limit' => ['shape' => 'DescribeLimit']]], 'DescribeExportTasksResponse' => ['type' => 'structure', 'members' => ['exportTasks' => ['shape' => 'ExportTasks'], 'nextToken' => ['shape' => 'NextToken']]], 'DescribeLimit' => ['type' => 'integer', 'max' => 50, 'min' => 1], 'DescribeLogGroupsRequest' => ['type' => 'structure', 'members' => ['logGroupNamePrefix' => ['shape' => 'LogGroupName'], 'nextToken' => ['shape' => 'NextToken'], 'limit' => ['shape' => 'DescribeLimit']]], 'DescribeLogGroupsResponse' => ['type' => 'structure', 'members' => ['logGroups' => ['shape' => 'LogGroups'], 'nextToken' => ['shape' => 'NextToken']]], 'DescribeLogStreamsRequest' => ['type' => 'structure', 'required' => ['logGroupName'], 'members' => ['logGroupName' => ['shape' => 'LogGroupName'], 'logStreamNamePrefix' => ['shape' => 'LogStreamName'], 'orderBy' => ['shape' => 'OrderBy'], 'descending' => ['shape' => 'Descending'], 'nextToken' => ['shape' => 'NextToken'], 'limit' => ['shape' => 'DescribeLimit']]], 'DescribeLogStreamsResponse' => ['type' => 'structure', 'members' => ['logStreams' => ['shape' => 'LogStreams'], 'nextToken' => ['shape' => 'NextToken']]], 'DescribeMetricFiltersRequest' => ['type' => 'structure', 'members' => ['logGroupName' => ['shape' => 'LogGroupName'], 'filterNamePrefix' => ['shape' => 'FilterName'], 'nextToken' => ['shape' => 'NextToken'], 'limit' => ['shape' => 'DescribeLimit'], 'metricName' => ['shape' => 'MetricName'], 'metricNamespace' => ['shape' => 'MetricNamespace']]], 'DescribeMetricFiltersResponse' => ['type' => 'structure', 'members' => ['metricFilters' => ['shape' => 'MetricFilters'], 'nextToken' => ['shape' => 'NextToken']]], 'DescribeQueriesMaxResults' => ['type' => 'integer', 'max' => 1000, 'min' => 1], 'DescribeQueriesRequest' => ['type' => 'structure', 'members' => ['logGroupName' => ['shape' => 'LogGroupName'], 'status' => ['shape' => 'QueryStatus'], 'maxResults' => ['shape' => 'DescribeQueriesMaxResults'], 'nextToken' => ['shape' => 'NextToken']]], 'DescribeQueriesResponse' => ['type' => 'structure', 'members' => ['queries' => ['shape' => 'QueryInfoList'], 'nextToken' => ['shape' => 'NextToken']]], 'DescribeResourcePoliciesRequest' => ['type' => 'structure', 'members' => ['nextToken' => ['shape' => 'NextToken'], 'limit' => ['shape' => 'DescribeLimit']]], 'DescribeResourcePoliciesResponse' => ['type' => 'structure', 'members' => ['resourcePolicies' => ['shape' => 'ResourcePolicies'], 'nextToken' => ['shape' => 'NextToken']]], 'DescribeSubscriptionFiltersRequest' => ['type' => 'structure', 'required' => ['logGroupName'], 'members' => ['logGroupName' => ['shape' => 'LogGroupName'], 'filterNamePrefix' => ['shape' => 'FilterName'], 'nextToken' => ['shape' => 'NextToken'], 'limit' => ['shape' => 'DescribeLimit']]], 'DescribeSubscriptionFiltersResponse' => ['type' => 'structure', 'members' => ['subscriptionFilters' => ['shape' => 'SubscriptionFilters'], 'nextToken' => ['shape' => 'NextToken']]], 'Destination' => ['type' => 'structure', 'members' => ['destinationName' => ['shape' => 'DestinationName'], 'targetArn' => ['shape' => 'TargetArn'], 'roleArn' => ['shape' => 'RoleArn'], 'accessPolicy' => ['shape' => 'AccessPolicy'], 'arn' => ['shape' => 'Arn'], 'creationTime' => ['shape' => 'Timestamp']]], 'DestinationArn' => ['type' => 'string', 'min' => 1], 'DestinationName' => ['type' => 'string', 'max' => 512, 'min' => 1, 'pattern' => '[^:*]*'], 'Destinations' => ['type' => 'list', 'member' => ['shape' => 'Destination']], 'DisassociateKmsKeyRequest' => ['type' => 'structure', 'required' => ['logGroupName'], 'members' => ['logGroupName' => ['shape' => 'LogGroupName']]], 'Distribution' => ['type' => 'string', 'enum' => ['Random', 'ByLogStream']], 'EventId' => ['type' => 'string'], 'EventMessage' => ['type' => 'string', 'min' => 1], 'EventNumber' => ['type' => 'long'], 'EventsLimit' => ['type' => 'integer', 'max' => 10000, 'min' => 1], 'ExportDestinationBucket' => ['type' => 'string', 'max' => 512, 'min' => 1], 'ExportDestinationPrefix' => ['type' => 'string'], 'ExportTask' => ['type' => 'structure', 'members' => ['taskId' => ['shape' => 'ExportTaskId'], 'taskName' => ['shape' => 'ExportTaskName'], 'logGroupName' => ['shape' => 'LogGroupName'], 'from' => ['shape' => 'Timestamp'], 'to' => ['shape' => 'Timestamp'], 'destination' => ['shape' => 'ExportDestinationBucket'], 'destinationPrefix' => ['shape' => 'ExportDestinationPrefix'], 'status' => ['shape' => 'ExportTaskStatus'], 'executionInfo' => ['shape' => 'ExportTaskExecutionInfo']]], 'ExportTaskExecutionInfo' => ['type' => 'structure', 'members' => ['creationTime' => ['shape' => 'Timestamp'], 'completionTime' => ['shape' => 'Timestamp']]], 'ExportTaskId' => ['type' => 'string', 'max' => 512, 'min' => 1], 'ExportTaskName' => ['type' => 'string', 'max' => 512, 'min' => 1], 'ExportTaskStatus' => ['type' => 'structure', 'members' => ['code' => ['shape' => 'ExportTaskStatusCode'], 'message' => ['shape' => 'ExportTaskStatusMessage']]], 'ExportTaskStatusCode' => ['type' => 'string', 'enum' => ['CANCELLED', 'COMPLETED', 'FAILED', 'PENDING', 'PENDING_CANCEL', 'RUNNING']], 'ExportTaskStatusMessage' => ['type' => 'string'], 'ExportTasks' => ['type' => 'list', 'member' => ['shape' => 'ExportTask']], 'ExtractedValues' => ['type' => 'map', 'key' => ['shape' => 'Token'], 'value' => ['shape' => 'Value']], 'Field' => ['type' => 'string'], 'FilterCount' => ['type' => 'integer'], 'FilterLogEventsRequest' => ['type' => 'structure', 'required' => ['logGroupName'], 'members' => ['logGroupName' => ['shape' => 'LogGroupName'], 'logStreamNames' => ['shape' => 'InputLogStreamNames'], 'logStreamNamePrefix' => ['shape' => 'LogStreamName'], 'startTime' => ['shape' => 'Timestamp'], 'endTime' => ['shape' => 'Timestamp'], 'filterPattern' => ['shape' => 'FilterPattern'], 'nextToken' => ['shape' => 'NextToken'], 'limit' => ['shape' => 'EventsLimit'], 'interleaved' => ['shape' => 'Interleaved']]], 'FilterLogEventsResponse' => ['type' => 'structure', 'members' => ['events' => ['shape' => 'FilteredLogEvents'], 'searchedLogStreams' => ['shape' => 'SearchedLogStreams'], 'nextToken' => ['shape' => 'NextToken']]], 'FilterName' => ['type' => 'string', 'max' => 512, 'min' => 1, 'pattern' => '[^:*]*'], 'FilterPattern' => ['type' => 'string', 'max' => 1024, 'min' => 0], 'FilteredLogEvent' => ['type' => 'structure', 'members' => ['logStreamName' => ['shape' => 'LogStreamName'], 'timestamp' => ['shape' => 'Timestamp'], 'message' => ['shape' => 'EventMessage'], 'ingestionTime' => ['shape' => 'Timestamp'], 'eventId' => ['shape' => 'EventId']]], 'FilteredLogEvents' => ['type' => 'list', 'member' => ['shape' => 'FilteredLogEvent']], 'GetLogEventsRequest' => ['type' => 'structure', 'required' => ['logGroupName', 'logStreamName'], 'members' => ['logGroupName' => ['shape' => 'LogGroupName'], 'logStreamName' => ['shape' => 'LogStreamName'], 'startTime' => ['shape' => 'Timestamp'], 'endTime' => ['shape' => 'Timestamp'], 'nextToken' => ['shape' => 'NextToken'], 'limit' => ['shape' => 'EventsLimit'], 'startFromHead' => ['shape' => 'StartFromHead']]], 'GetLogEventsResponse' => ['type' => 'structure', 'members' => ['events' => ['shape' => 'OutputLogEvents'], 'nextForwardToken' => ['shape' => 'NextToken'], 'nextBackwardToken' => ['shape' => 'NextToken']]], 'GetLogGroupFieldsRequest' => ['type' => 'structure', 'required' => ['logGroupName'], 'members' => ['logGroupName' => ['shape' => 'LogGroupName'], 'time' => ['shape' => 'Timestamp']]], 'GetLogGroupFieldsResponse' => ['type' => 'structure', 'members' => ['logGroupFields' => ['shape' => 'LogGroupFieldList']]], 'GetLogRecordRequest' => ['type' => 'structure', 'required' => ['logRecordPointer'], 'members' => ['logRecordPointer' => ['shape' => 'LogRecordPointer']]], 'GetLogRecordResponse' => ['type' => 'structure', 'members' => ['logRecord' => ['shape' => 'LogRecord']]], 'GetQueryResultsRequest' => ['type' => 'structure', 'required' => ['queryId'], 'members' => ['queryId' => ['shape' => 'QueryId']]], 'GetQueryResultsResponse' => ['type' => 'structure', 'members' => ['results' => ['shape' => 'QueryResults'], 'statistics' => ['shape' => 'QueryStatistics'], 'status' => ['shape' => 'QueryStatus']]], 'InputLogEvent' => ['type' => 'structure', 'required' => ['timestamp', 'message'], 'members' => ['timestamp' => ['shape' => 'Timestamp'], 'message' => ['shape' => 'EventMessage']]], 'InputLogEvents' => ['type' => 'list', 'member' => ['shape' => 'InputLogEvent'], 'max' => 10000, 'min' => 1], 'InputLogStreamNames' => ['type' => 'list', 'member' => ['shape' => 'LogStreamName'], 'max' => 100, 'min' => 1], 'Interleaved' => ['type' => 'boolean'], 'InvalidOperationException' => ['type' => 'structure', 'members' => [], 'exception' => \true], 'InvalidParameterException' => ['type' => 'structure', 'members' => [], 'exception' => \true], 'InvalidSequenceTokenException' => ['type' => 'structure', 'members' => ['expectedSequenceToken' => ['shape' => 'SequenceToken']], 'exception' => \true], 'KmsKeyId' => ['type' => 'string', 'max' => 256], 'LimitExceededException' => ['type' => 'structure', 'members' => [], 'exception' => \true], 'ListTagsLogGroupRequest' => ['type' => 'structure', 'required' => ['logGroupName'], 'members' => ['logGroupName' => ['shape' => 'LogGroupName']]], 'ListTagsLogGroupResponse' => ['type' => 'structure', 'members' => ['tags' => ['shape' => 'Tags']]], 'LogEventIndex' => ['type' => 'integer'], 'LogGroup' => ['type' => 'structure', 'members' => ['logGroupName' => ['shape' => 'LogGroupName'], 'creationTime' => ['shape' => 'Timestamp'], 'retentionInDays' => ['shape' => 'Days'], 'metricFilterCount' => ['shape' => 'FilterCount'], 'arn' => ['shape' => 'Arn'], 'storedBytes' => ['shape' => 'StoredBytes'], 'kmsKeyId' => ['shape' => 'KmsKeyId']]], 'LogGroupField' => ['type' => 'structure', 'members' => ['name' => ['shape' => 'Field'], 'percent' => ['shape' => 'Percentage']]], 'LogGroupFieldList' => ['type' => 'list', 'member' => ['shape' => 'LogGroupField']], 'LogGroupName' => ['type' => 'string', 'max' => 512, 'min' => 1, 'pattern' => '[\\.\\-_/#A-Za-z0-9]+'], 'LogGroups' => ['type' => 'list', 'member' => ['shape' => 'LogGroup']], 'LogRecord' => ['type' => 'map', 'key' => ['shape' => 'Field'], 'value' => ['shape' => 'Value']], 'LogRecordPointer' => ['type' => 'string'], 'LogStream' => ['type' => 'structure', 'members' => ['logStreamName' => ['shape' => 'LogStreamName'], 'creationTime' => ['shape' => 'Timestamp'], 'firstEventTimestamp' => ['shape' => 'Timestamp'], 'lastEventTimestamp' => ['shape' => 'Timestamp'], 'lastIngestionTime' => ['shape' => 'Timestamp'], 'uploadSequenceToken' => ['shape' => 'SequenceToken'], 'arn' => ['shape' => 'Arn'], 'storedBytes' => ['shape' => 'StoredBytes']]], 'LogStreamName' => ['type' => 'string', 'max' => 512, 'min' => 1, 'pattern' => '[^:*]*'], 'LogStreamSearchedCompletely' => ['type' => 'boolean'], 'LogStreams' => ['type' => 'list', 'member' => ['shape' => 'LogStream']], 'MalformedQueryException' => ['type' => 'structure', 'members' => ['queryCompileError' => ['shape' => 'QueryCompileError']], 'exception' => \true], 'Message' => ['type' => 'string'], 'MetricFilter' => ['type' => 'structure', 'members' => ['filterName' => ['shape' => 'FilterName'], 'filterPattern' => ['shape' => 'FilterPattern'], 'metricTransformations' => ['shape' => 'MetricTransformations'], 'creationTime' => ['shape' => 'Timestamp'], 'logGroupName' => ['shape' => 'LogGroupName']]], 'MetricFilterMatchRecord' => ['type' => 'structure', 'members' => ['eventNumber' => ['shape' => 'EventNumber'], 'eventMessage' => ['shape' => 'EventMessage'], 'extractedValues' => ['shape' => 'ExtractedValues']]], 'MetricFilterMatches' => ['type' => 'list', 'member' => ['shape' => 'MetricFilterMatchRecord']], 'MetricFilters' => ['type' => 'list', 'member' => ['shape' => 'MetricFilter']], 'MetricName' => ['type' => 'string', 'max' => 255, 'pattern' => '[^:*$]*'], 'MetricNamespace' => ['type' => 'string', 'max' => 255, 'pattern' => '[^:*$]*'], 'MetricTransformation' => ['type' => 'structure', 'required' => ['metricName', 'metricNamespace', 'metricValue'], 'members' => ['metricName' => ['shape' => 'MetricName'], 'metricNamespace' => ['shape' => 'MetricNamespace'], 'metricValue' => ['shape' => 'MetricValue'], 'defaultValue' => ['shape' => 'DefaultValue']]], 'MetricTransformations' => ['type' => 'list', 'member' => ['shape' => 'MetricTransformation'], 'max' => 1, 'min' => 1], 'MetricValue' => ['type' => 'string', 'max' => 100], 'NextToken' => ['type' => 'string', 'min' => 1], 'OperationAbortedException' => ['type' => 'structure', 'members' => [], 'exception' => \true], 'OrderBy' => ['type' => 'string', 'enum' => ['LogStreamName', 'LastEventTime']], 'OutputLogEvent' => ['type' => 'structure', 'members' => ['timestamp' => ['shape' => 'Timestamp'], 'message' => ['shape' => 'EventMessage'], 'ingestionTime' => ['shape' => 'Timestamp']]], 'OutputLogEvents' => ['type' => 'list', 'member' => ['shape' => 'OutputLogEvent']], 'Percentage' => ['type' => 'integer', 'max' => 100, 'min' => 0], 'PolicyDocument' => ['type' => 'string', 'max' => 5120, 'min' => 1], 'PolicyName' => ['type' => 'string'], 'PutDestinationPolicyRequest' => ['type' => 'structure', 'required' => ['destinationName', 'accessPolicy'], 'members' => ['destinationName' => ['shape' => 'DestinationName'], 'accessPolicy' => ['shape' => 'AccessPolicy']]], 'PutDestinationRequest' => ['type' => 'structure', 'required' => ['destinationName', 'targetArn', 'roleArn'], 'members' => ['destinationName' => ['shape' => 'DestinationName'], 'targetArn' => ['shape' => 'TargetArn'], 'roleArn' => ['shape' => 'RoleArn']]], 'PutDestinationResponse' => ['type' => 'structure', 'members' => ['destination' => ['shape' => 'Destination']]], 'PutLogEventsRequest' => ['type' => 'structure', 'required' => ['logGroupName', 'logStreamName', 'logEvents'], 'members' => ['logGroupName' => ['shape' => 'LogGroupName'], 'logStreamName' => ['shape' => 'LogStreamName'], 'logEvents' => ['shape' => 'InputLogEvents'], 'sequenceToken' => ['shape' => 'SequenceToken']]], 'PutLogEventsResponse' => ['type' => 'structure', 'members' => ['nextSequenceToken' => ['shape' => 'SequenceToken'], 'rejectedLogEventsInfo' => ['shape' => 'RejectedLogEventsInfo']]], 'PutMetricFilterRequest' => ['type' => 'structure', 'required' => ['logGroupName', 'filterName', 'filterPattern', 'metricTransformations'], 'members' => ['logGroupName' => ['shape' => 'LogGroupName'], 'filterName' => ['shape' => 'FilterName'], 'filterPattern' => ['shape' => 'FilterPattern'], 'metricTransformations' => ['shape' => 'MetricTransformations']]], 'PutResourcePolicyRequest' => ['type' => 'structure', 'members' => ['policyName' => ['shape' => 'PolicyName'], 'policyDocument' => ['shape' => 'PolicyDocument']]], 'PutResourcePolicyResponse' => ['type' => 'structure', 'members' => ['resourcePolicy' => ['shape' => 'ResourcePolicy']]], 'PutRetentionPolicyRequest' => ['type' => 'structure', 'required' => ['logGroupName', 'retentionInDays'], 'members' => ['logGroupName' => ['shape' => 'LogGroupName'], 'retentionInDays' => ['shape' => 'Days']]], 'PutSubscriptionFilterRequest' => ['type' => 'structure', 'required' => ['logGroupName', 'filterName', 'filterPattern', 'destinationArn'], 'members' => ['logGroupName' => ['shape' => 'LogGroupName'], 'filterName' => ['shape' => 'FilterName'], 'filterPattern' => ['shape' => 'FilterPattern'], 'destinationArn' => ['shape' => 'DestinationArn'], 'roleArn' => ['shape' => 'RoleArn'], 'distribution' => ['shape' => 'Distribution']]], 'QueryCharOffset' => ['type' => 'integer'], 'QueryCompileError' => ['type' => 'structure', 'members' => ['location' => ['shape' => 'QueryCompileErrorLocation'], 'message' => ['shape' => 'Message']]], 'QueryCompileErrorLocation' => ['type' => 'structure', 'members' => ['startCharOffset' => ['shape' => 'QueryCharOffset'], 'endCharOffset' => ['shape' => 'QueryCharOffset']]], 'QueryId' => ['type' => 'string', 'max' => 256, 'min' => 0], 'QueryInfo' => ['type' => 'structure', 'members' => ['queryId' => ['shape' => 'QueryId'], 'queryString' => ['shape' => 'QueryString'], 'status' => ['shape' => 'QueryStatus'], 'createTime' => ['shape' => 'Timestamp'], 'logGroupName' => ['shape' => 'LogGroupName']]], 'QueryInfoList' => ['type' => 'list', 'member' => ['shape' => 'QueryInfo']], 'QueryResults' => ['type' => 'list', 'member' => ['shape' => 'ResultRows']], 'QueryStatistics' => ['type' => 'structure', 'members' => ['recordsMatched' => ['shape' => 'StatsValue'], 'recordsScanned' => ['shape' => 'StatsValue'], 'bytesScanned' => ['shape' => 'StatsValue']]], 'QueryStatus' => ['type' => 'string', 'enum' => ['Scheduled', 'Running', 'Complete', 'Failed', 'Cancelled']], 'QueryString' => ['type' => 'string', 'max' => 2048, 'min' => 0], 'RejectedLogEventsInfo' => ['type' => 'structure', 'members' => ['tooNewLogEventStartIndex' => ['shape' => 'LogEventIndex'], 'tooOldLogEventEndIndex' => ['shape' => 'LogEventIndex'], 'expiredLogEventEndIndex' => ['shape' => 'LogEventIndex']]], 'ResourceAlreadyExistsException' => ['type' => 'structure', 'members' => [], 'exception' => \true], 'ResourceNotFoundException' => ['type' => 'structure', 'members' => [], 'exception' => \true], 'ResourcePolicies' => ['type' => 'list', 'member' => ['shape' => 'ResourcePolicy']], 'ResourcePolicy' => ['type' => 'structure', 'members' => ['policyName' => ['shape' => 'PolicyName'], 'policyDocument' => ['shape' => 'PolicyDocument'], 'lastUpdatedTime' => ['shape' => 'Timestamp']]], 'ResultField' => ['type' => 'structure', 'members' => ['field' => ['shape' => 'Field'], 'value' => ['shape' => 'Value']]], 'ResultRows' => ['type' => 'list', 'member' => ['shape' => 'ResultField']], 'RoleArn' => ['type' => 'string', 'min' => 1], 'SearchedLogStream' => ['type' => 'structure', 'members' => ['logStreamName' => ['shape' => 'LogStreamName'], 'searchedCompletely' => ['shape' => 'LogStreamSearchedCompletely']]], 'SearchedLogStreams' => ['type' => 'list', 'member' => ['shape' => 'SearchedLogStream']], 'SequenceToken' => ['type' => 'string', 'min' => 1], 'ServiceUnavailableException' => ['type' => 'structure', 'members' => [], 'exception' => \true, 'fault' => \true], 'StartFromHead' => ['type' => 'boolean'], 'StartQueryRequest' => ['type' => 'structure', 'required' => ['logGroupName', 'startTime', 'endTime', 'queryString'], 'members' => ['logGroupName' => ['shape' => 'LogGroupName'], 'startTime' => ['shape' => 'Timestamp'], 'endTime' => ['shape' => 'Timestamp'], 'queryString' => ['shape' => 'QueryString'], 'limit' => ['shape' => 'EventsLimit']]], 'StartQueryResponse' => ['type' => 'structure', 'members' => ['queryId' => ['shape' => 'QueryId']]], 'StatsValue' => ['type' => 'double'], 'StopQueryRequest' => ['type' => 'structure', 'required' => ['queryId'], 'members' => ['queryId' => ['shape' => 'QueryId']]], 'StopQueryResponse' => ['type' => 'structure', 'members' => ['success' => ['shape' => 'Success']]], 'StoredBytes' => ['type' => 'long', 'min' => 0], 'SubscriptionFilter' => ['type' => 'structure', 'members' => ['filterName' => ['shape' => 'FilterName'], 'logGroupName' => ['shape' => 'LogGroupName'], 'filterPattern' => ['shape' => 'FilterPattern'], 'destinationArn' => ['shape' => 'DestinationArn'], 'roleArn' => ['shape' => 'RoleArn'], 'distribution' => ['shape' => 'Distribution'], 'creationTime' => ['shape' => 'Timestamp']]], 'SubscriptionFilters' => ['type' => 'list', 'member' => ['shape' => 'SubscriptionFilter']], 'Success' => ['type' => 'boolean'], 'TagKey' => ['type' => 'string', 'max' => 128, 'min' => 1, 'pattern' => '^([\\p{L}\\p{Z}\\p{N}_.:/=+\\-@]+)$'], 'TagList' => ['type' => 'list', 'member' => ['shape' => 'TagKey'], 'min' => 1], 'TagLogGroupRequest' => ['type' => 'structure', 'required' => ['logGroupName', 'tags'], 'members' => ['logGroupName' => ['shape' => 'LogGroupName'], 'tags' => ['shape' => 'Tags']]], 'TagValue' => ['type' => 'string', 'max' => 256, 'pattern' => '^([\\p{L}\\p{Z}\\p{N}_.:/=+\\-@]*)$'], 'Tags' => ['type' => 'map', 'key' => ['shape' => 'TagKey'], 'value' => ['shape' => 'TagValue'], 'max' => 50, 'min' => 1], 'TargetArn' => ['type' => 'string', 'min' => 1], 'TestEventMessages' => ['type' => 'list', 'member' => ['shape' => 'EventMessage'], 'max' => 50, 'min' => 1], 'TestMetricFilterRequest' => ['type' => 'structure', 'required' => ['filterPattern', 'logEventMessages'], 'members' => ['filterPattern' => ['shape' => 'FilterPattern'], 'logEventMessages' => ['shape' => 'TestEventMessages']]], 'TestMetricFilterResponse' => ['type' => 'structure', 'members' => ['matches' => ['shape' => 'MetricFilterMatches']]], 'Timestamp' => ['type' => 'long', 'min' => 0], 'Token' => ['type' => 'string'], 'UnrecognizedClientException' => ['type' => 'structure', 'members' => [], 'exception' => \true], 'UntagLogGroupRequest' => ['type' => 'structure', 'required' => ['logGroupName', 'tags'], 'members' => ['logGroupName' => ['shape' => 'LogGroupName'], 'tags' => ['shape' => 'TagList']]], 'Value' => ['type' => 'string']]];
