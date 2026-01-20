-- CreateTable
CREATE TABLE "Reservation" (
    "id" TEXT NOT NULL PRIMARY KEY,
    "reservationId" TEXT NOT NULL,
    "chatId" TEXT,
    "dateBR" TEXT,
    "dateISO" TEXT,
    "peopleCount" INTEGER,
    "wantsStay" BOOLEAN,
    "suiteChoice" TEXT,
    "status" TEXT NOT NULL DEFAULT 'PENDING_PAYMENT',
    "totalAmount" INTEGER NOT NULL DEFAULT 0,
    "paymentToken" TEXT,
    "paymentUrl" TEXT,
    "paymentStatus" TEXT NOT NULL DEFAULT 'PENDING',
    "asaasPaymentId" TEXT,
    "notes" TEXT NOT NULL DEFAULT '',
    "createdAt" DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    "updatedAt" DATETIME NOT NULL
);

-- CreateTable
CREATE TABLE "ReservationPerson" (
    "id" TEXT NOT NULL PRIMARY KEY,
    "reservationDbId" TEXT NOT NULL,
    "name" TEXT NOT NULL,
    "cpf" TEXT NOT NULL,
    "cpfMasked" TEXT NOT NULL,
    "birthDateBR" TEXT NOT NULL,
    "birthDateISO" TEXT NOT NULL,
    CONSTRAINT "ReservationPerson_reservationDbId_fkey" FOREIGN KEY ("reservationDbId") REFERENCES "Reservation" ("id") ON DELETE CASCADE ON UPDATE CASCADE
);

-- CreateTable
CREATE TABLE "WebhookEvent" (
    "id" TEXT NOT NULL PRIMARY KEY,
    "provider" TEXT NOT NULL,
    "eventType" TEXT NOT NULL,
    "externalId" TEXT,
    "payload" TEXT NOT NULL,
    "createdAt" DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
);

-- CreateIndex
CREATE UNIQUE INDEX "Reservation_reservationId_key" ON "Reservation"("reservationId");

-- CreateIndex
CREATE UNIQUE INDEX "ReservationPerson_reservationDbId_cpf_key" ON "ReservationPerson"("reservationDbId", "cpf");
