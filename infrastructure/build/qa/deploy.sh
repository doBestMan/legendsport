#!/bin/bash

CLOUDSDK_COMPUTE_REGION='us-central1'
CLOUDSDK_CONTAINER_CLUSTER='primary'
BACKEND_IMAGE="gcr.io/$PROJECT_ID/php:$SHORT_SHA"
FRONTEND_IMAGE="gcr.io/$PROJECT_ID/nginx:$SHORT_SHA"
NAMESPACE="qaod-$BRANCH_NAME"
DOMAIN_NAME="$BRANCH_NAME.qa.legendsports.bet"
DATABASE_NAME="lsqa-$BRANCH_NAME"

mkdir /tmp/qa

cp infrastructure/kubernetes/qa/*.yaml /tmp/qa

echo "Deploying: $BACKEND_IMAGE and $FRONTEND_IMAGE"

gcloud sql databases create $DATABASE_NAME --instance="production"

REPLACEMENTS="\
s!######BACKEND_IMAGE######!${BACKEND_IMAGE}!g;\
s!######FRONTEND_IMAGE######!${FRONTEND_IMAGE}!g;\
s!######DOMAIN_NAME######!${DOMAIN_NAME}!g;\
s!######DATABASE_NAME######!${DATABASE_NAME}!g;\
s!######NAMESPACE######!${NAMESPACE}!g\
"

sed $REPLACEMENTS ./infrastructure/kubernetes/qa/templated/php-fpm.yaml  > /tmp/qa/php-fpm.yaml
sed $REPLACEMENTS ./infrastructure/kubernetes/qa/templated/scheduler.yaml > /tmp/qa/scheduler.yaml
sed $REPLACEMENTS ./infrastructure/kubernetes/qa/templated/websockets.yaml > /tmp/qa/websockets.yaml
sed $REPLACEMENTS ./infrastructure/kubernetes/qa/templated/configmap.yaml > /tmp/qa/configmap.yaml

gcloud container clusters get-credentials --region "$CLOUDSDK_COMPUTE_REGION" "$CLOUDSDK_CONTAINER_CLUSTER"

kubectl apply -f /tmp/qa -n qa

sed $REPLACEMENTS ./infrastructure/kubernetes/qa/templated/ingress.yaml > /tmp/qa/ingress.yaml

kubectl patch -f /tmp/qa/ingress.yaml -n qa
