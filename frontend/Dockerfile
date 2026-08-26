FROM node:22-alpine

WORKDIR /app

# Se copian sólo los manifiestos primero: mientras no cambien, Docker
# reutiliza la capa de npm ci y no reinstala en cada build.
COPY package*.json ./
RUN npm ci

COPY . .

EXPOSE 5173

# --host 0.0.0.0: por defecto Vite escucha sólo en localhost dentro del
# contenedor y no sería alcanzable desde el host.
CMD ["npm", "run", "dev", "--", "--host", "0.0.0.0"]
