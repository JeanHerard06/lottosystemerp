class OfflineTicket {
  final String localUuid;
  final int? serverTicketId;
  final double totalAmount;
  final String syncStatus;
  final String? errorMessage;

  OfflineTicket({
    required this.localUuid,
    this.serverTicketId,
    required this.totalAmount,
    required this.syncStatus,
    this.errorMessage,
  });
}
