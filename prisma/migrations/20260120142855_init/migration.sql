/*
  Warnings:

  - You are about to drop the `ReservationPerson` table. If the table is not empty, all the data it contains will be lost.
  - You are about to drop the `WebhookEvent` table. If the table is not empty, all the data it contains will be lost.
  - You are about to drop the column `asaasStatus` on the `Payment` table. All the data in the column will be lost.
  - You are about to drop the column `payUrl` on the `Payment` table. All the data in the column will be lost.
  - Made the column `reservationId` on table `Payment` required. This step will fail if there are existing NULL values in that column.

*/
-- DropIndex
DROP INDEX "ReservationPerson_reservationId_cpf_key";

-- DropTable
PRAGMA foreign_keys=off;
DROP TABLE "ReservationPerson";
PRAGMA foreign_keys=on;

-- DropTable
PRAGMA foreign_keys=off;
DROP TABLE "WebhookEvent";
PRAGMA foreign_keys=on;

-- CreateTable
CREATE TABLE "Person" (
    "id" TEXT NOT NULL PRIMARY KEY,
    "reservationId" TEXT NOT NULL,
    "name" TEXT NOT NULL,
    "cpf" TEXT NOT NULL,
    "cpfMasked" TEXT NOT NULL,
    "dobBR" TEXT NOT NULL,
    "dobISO" TEXT NOT NULL,
    "createdAt" DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT "Person_reservationId_fkey" FOREIGN KEY ("reservationId") REFERENCES "Reservation" ("reservationId") ON DELETE CASCADE ON UPDATE CASCADE
);

-- RedefineTables
PRAGMA defer_foreign_keys=ON;
PRAGMA foreign_keys=OFF;
CREATE TABLE "new_Payment" (
    "id" TEXT NOT NULL PRIMARY KEY,
    "reservationId" TEXT NOT NULL,
    "provider" TEXT NOT NULL DEFAULT 'ASAAS',
    "token" TEXT,
    "asaasCustomerId" TEXT,
    "asaasPaymentId" TEXT,
    "status" TEXT NOT NULL DEFAULT 'PENDING',
    "billingType" TEXT,
    "amount" INTEGER NOT NULL DEFAULT 0,
    "invoiceUrl" TEXT,
    "bankSlipUrl" TEXT,
    "pixQrCode" TEXT,
    "pixPayload" TEXT,
    "rawWebhook" TEXT,
    "createdAt" DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    "updatedAt" DATETIME NOT NULL,
    CONSTRAINT "Payment_reservationId_fkey" FOREIGN KEY ("reservationId") REFERENCES "Reservation" ("reservationId") ON DELETE CASCADE ON UPDATE CASCADE
);
INSERT INTO "new_Payment" ("amount", "asaasCustomerId", "asaasPaymentId", "createdAt", "id", "reservationId", "status", "token", "updatedAt") SELECT coalesce("amount", 0) AS "amount", "asaasCustomerId", "asaasPaymentId", "createdAt", "id", "reservationId", "status", "token", "updatedAt" FROM "Payment";
DROP TABLE "Payment";
ALTER TABLE "new_Payment" RENAME TO "Payment";
CREATE UNIQUE INDEX "Payment_reservationId_key" ON "Payment"("reservationId");
CREATE TABLE "new_Reservation" (
    "id" TEXT NOT NULL PRIMARY KEY,
    "reservationId" TEXT NOT NULL,
    "chatId" TEXT,
    "source" TEXT DEFAULT 'WHATSAPP',
    "dateBR" TEXT,
    "dateISO" TEXT,
    "peopleCount" INTEGER,
    "wantsStay" BOOLEAN,
    "suiteChoice" TEXT,
    "status" TEXT NOT NULL DEFAULT 'PENDING_PAYMENT',
    "totalAmount" INTEGER NOT NULL DEFAULT 1000,
    "paymentToken" TEXT,
    "paymentUrl" TEXT,
    "paymentStatus" TEXT NOT NULL DEFAULT 'PENDING',
    "notes" TEXT NOT NULL DEFAULT '',
    "createdAt" DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    "updatedAt" DATETIME NOT NULL
);
INSERT INTO "new_Reservation" ("chatId", "createdAt", "dateBR", "dateISO", "id", "notes", "paymentStatus", "paymentToken", "paymentUrl", "peopleCount", "reservationId", "status", "suiteChoice", "totalAmount", "updatedAt", "wantsStay") SELECT "chatId", "createdAt", "dateBR", "dateISO", "id", coalesce("notes", '') AS "notes", "paymentStatus", "paymentToken", "paymentUrl", "peopleCount", "reservationId", "status", "suiteChoice", coalesce("totalAmount", 1000) AS "totalAmount", "updatedAt", "wantsStay" FROM "Reservation";
DROP TABLE "Reservation";
ALTER TABLE "new_Reservation" RENAME TO "Reservation";
CREATE UNIQUE INDEX "Reservation_reservationId_key" ON "Reservation"("reservationId");
PRAGMA foreign_keys=ON;
PRAGMA defer_foreign_keys=OFF;
