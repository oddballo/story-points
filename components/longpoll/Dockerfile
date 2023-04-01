FROM node:19.8.1-alpine3.16
COPY package.json .
COPY package-lock.json .
RUN npm install
COPY server.js .
ENTRYPOINT ["npm", "start"]
