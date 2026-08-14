import CreateDiscussionForm from "@/components/CreateDiscussionForm";
import Header from "@/components/Header";

export default function CreateDiscussionPage() {
  return (
    <div className="min-h-screen w-full bg-white">
      <Header />
      <main className="mx-auto flex min-h-[calc(100vh-128px)] w-full justify-center">
        <CreateDiscussionForm />
      </main>
    </div>
  );
}
