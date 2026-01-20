/*
  Warnings:

  - You are about to drop the column `asaasPaymentId` on the `Reservation` table. All the data in the column will be lost.
  - You are about to drop the column `reservationDbId` on the `ReservationPerson` table. All the data in the column will be lost.
  - You are about to drop the column `eventType` on the `WebhookEvent` table. All the data in the column will be lost.
  - Added the required column `reservationId` to the `ReservationPerson` table without a default value. This is not possible if the table is not empty.
  - Added the required column `event` to the `WebhookEvent` table without a default value. This is not possible if the table is not empty.

*/
-- CreateTable
CREATE TABLE "Payment" (
    "id" TEXT NOT NULL PRIMARY KEY,
    "token" TEXT NOT NULL,
    "status" TEXT NOT NULL DEFAULT 'PENDING',
    "amount" INTEGER,
    "payUrl" TEXT,
    "asaasCustomerId" TEXT,
    "asaasPaymentId" TEXT,
    "asaasStatus" TEXT,
    "reservationId" TEXT,
    "createdAt" DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    "updatedAt" DATETIME NOT NULL,
    CONSTRAINT "Payment_reservationId_fkey" FOREIGN KEY ("reservationId") REFERENCES "Reservation" ("id") ON DELETE SET NULL ON UPDATE CASCADE
);

-- RedefineTables
PRAGMA defer_foreign_keys=ON;
PRAGMA foreign_keys=OFF;
CREATE TABLE "new_Reservation" (
    "id" TEXT NOT NULL PRIMARY KEY,
    "reservationId" TEXT NOT NULL,
    "chatId" TEXT,
    "dateBR" TEXT,
    "dateISO" TEXT,
    "peopleCount" INTEGER,
    "wantsStay" BOOLEAN,
    "suiteChoice" TEXT,
    "status" TEXT NOT NULL DEFAULT 'PENDING_PAYMENT',
    "totalAmount" INTEGER,
    "paymentToken" TEXT,
    "paymentUrl" TEXT,
    "paymentStatus" TEXT NOT NULL DEFAULT 'PENDING',
    "notes" TEXT,
    "createdAt" DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    "updatedAt" DATETIME NOT NULL
);
INSERT INTO "new_Reservation" ("chatId", "createdAt", "dateBR", "dateISO", "id", "notes", "paymentStatus", "paymentToken", "paymentUrl", "peopleCount", "reservationId", "status", "suiteChoice", "totalAmount", "updatedAt", "wantsStay") SELECT "chatId", "createdAt", "dateBR", "dateISO", "id", "notes", "paymentStatus", "paymentToken", "paymentUrl", "peopleCount", "reservationId", "status", "suiteChoice", "totalAmount", "updatedAt", "wantsStay" FROM "Reservation";
DROP TABLE "Reservation";
ALTER TABLE "new_Reservation" RENAME TO "Reservation";
CREATE UNIQUE INDEX "Reservation_reservationId_key" ON "Reservation"("reservationId");
CREATE UNIQUE INDEX "Reservation_paymentToken_key" ON "Reservation"("paymentToken");
CREATE TABLE "new_ReservationPerson" (
    "id" TEXT NOT NULL PRIMARY KEY,
    "reservationId" TEXT NOT NULL,
    "name" TEXT NOT NULL,
    "cpf" TEXT NOT NULL,
    "cpfMasked" TEXT,
    "birthDateISO" TEXT,
    "birthDateBR" TEXT,
    CONSTRAINT "ReservationPerson_reservationId_fkey" FOREIGN KEY ("reservationId") REFERENCES "Reservation" ("id") ON DELETE CASCADE ON UPDATE CASCADE
);
INSERT INTO "new_ReservationPerson" ("birthDateBR", "birthDateISO", "cpf", "cpfMasked", "id", "name") SELECT "birthDateBR", "birthDateISO", "cpf", "cpfMasked", "id", "name" FROM "ReservationPerson";
DROP TABLE "ReservationPerson";
ALTER TABLE "new_ReservationPerson" RENAME TO "ReservationPerson";
CREATE UNIQUE INDEX "ReservationPerson_reservationId_cpf_key" ON "ReservationPerson"("reservationId", "cpf");
CREATE TABLE "new_WebhookEvent" (
    "id" TEXT NOT NULL PRIMARY KEY,
    "provider" TEXT NOT NULL,
    "event" TEXT NOT NULL,
    "externalId" TEXT,
    "payload" TEXT NOT NULL,
    "createdAt" DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
);
INSERT INTO "new_WebhookEvent" ("createdAt", "externalId", "id", "payload", "provider") SELECT "createdAt", "externalId", "id", "payload", "provider" FROM "WebhookEvent";
DROP TABLE "WebhookEvent";
ALTER TABLE "new_WebhookEvent" RENAME TO "WebhookEvent";
PRAGMA foreign_keys=ON;
PRAGMA defer_foreign_keys=OFF;

-- CreateIndex
CREATE UNIQUE INDEX "Payment_token_key" ON "Payment"("token");

-- CreateIndex
CREATE UNIQUE INDEX "Payment_reservationId_key" ON "Payment"("reservationId");
