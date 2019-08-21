curl \
  -u "${APIMATIC_USR}" \
  -X POST \
  -F "file=@postman_collections/${POSTCOLL}.postman_collection.json" \
  https://apimatic.io/api/transform?format=swagger20 > "${SWAGOUT}-api.json";
