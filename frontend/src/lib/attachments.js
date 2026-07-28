// Prvata slika od prilozite na diskusijata, ili null ako nema.
export function getAttachmentImage(attachments = []) {
  const imageAttachment = attachments.find((attachment) => {
    const type = attachment.type ?? attachment.mime_type ?? attachment.mimeType ?? "";
    const url = attachment.imageUrl ?? attachment.url ?? attachment.path ?? "";

    return type.startsWith("image/") || /\.(avif|gif|jpe?g|png|webp|svg)$/i.test(url);
  });

  return imageAttachment?.imageUrl ?? imageAttachment?.url ?? imageAttachment?.path ?? null;
}
